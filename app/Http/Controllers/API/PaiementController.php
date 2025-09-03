<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payement;
use App\Services\CinetpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    public function initier(Request $request)
    {
        // Augmenter le temps d'exécution pour les API externes
        set_time_limit(60);

        try {
            // Utiliser le service mock temporairement pour éviter les timeouts
            $useMock = env('CINETPAY_USE_MOCK', true);
            $cinetpay = $useMock ? new MockCinetpayService() : new CinetpayService();

            Log::info('=== DEBUT PAIEMENT ===');
            Log::info('Using service:', ['type' => $useMock ? 'MOCK' : 'REAL']);
            Log::info('Request headers:', $request->headers->all());
            Log::info('Auth user:', auth()->user());
            Log::info('Token present:', $request->bearerToken() ? 'Yes' : 'No');
            Log::info('Request data:', $request->all());

            // Validation des données
            $validator = Validator::make($request->all(), [
                'montant' => 'required|numeric|min:100'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'error' => 'Données invalides',
                    'messages' => $validator->errors()
                ], 422);
            }

            // Vérifier que l'utilisateur est authentifié
            $user = auth()->user();
            if (!$user) {
                Log::error('User not authenticated');
                return response()->json([
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            Log::info('User authenticated successfully:', ['user_id' => $user->id]);

            $transactionId = uniqid("PAY-");
            Log::info('Generated transaction ID:', ['transaction_id' => $transactionId]);

            // Préparer les URLs de callback
            $returnUrl = url('/payment/success');
            $notifyUrl = url('/payment/notify');

            Log::info('Callback URLs:', [
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl
            ]);

            Log::info('Calling CinetPay service...');

            // Appel du service CinetPay avec gestion d'erreur
            $result = $cinetpay->initierPaiement(
                $request->montant,
                $transactionId,
                "Paiement commande Boulangerie Hi-Tech",
                $returnUrl,
                $notifyUrl
            );

            Log::info('CinetPay service response:', $result);

            // Vérifier la réponse de CinetPay
            if (!$result || (isset($result['code']) && $result['code'] !== '201')) {
                $errorMessage = isset($result['message']) ? $result['message'] : 'Erreur inconnue de CinetPay';
                Log::error('CinetPay error response:', $result);

                return response()->json([
                    'error' => 'Erreur CinetPay',
                    'message' => $errorMessage,
                    'cinetpay_response' => $result
                ], 500);
            }

            // Créer l'enregistrement de paiement
            $payement = Payement::create([
                'user_id'        => $user->id,
                'montant'        => $request->montant,
                'transaction_ref'=> $transactionId,
                'status'         => 'en_attente',
                'methode'        => 'CinetPay',
                'devise'         => 'XOF'
            ]);

            Log::info('Payment record created:', [
                'payment_id' => $payement->id,
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'montant' => $request->montant
            ]);

            Log::info('=== PAIEMENT INITIE AVEC SUCCES ===');

            return response()->json([
                'success' => true,
                'data' => $result,
                'transaction_id' => $transactionId,
                'payment_id' => $payement->id
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in payment initialization:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'initialisation du paiement',
                'message' => $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function notify(Request $request)
    {
        try {
            Log::info('=== NOTIFICATION PAIEMENT ===');
            Log::info('Notification data:', $request->all());

            $transactionId = $request->transaction_id;
            $status = $request->status == "ACCEPTED" ? "success" : "failed";

            Log::info('Processing notification:', [
                'transaction_id' => $transactionId,
                'status' => $status
            ]);

            $payement = Payement::where('transaction_ref', $transactionId)->first();

            if ($payement) {
                $payement->status = $status;
                $payement->statut = $status == "success" ? "valide" : "echoue";
                $payement->date_paiement = now();
                $payement->save();

                Log::info('Payment updated successfully:', [
                    'payment_id' => $payement->id,
                    'transaction_id' => $transactionId,
                    'new_status' => $status
                ]);
            } else {
                Log::warning('Payment not found for transaction:', ['transaction_id' => $transactionId]);
            }

            return response()->json(["message" => "ok"]);

        } catch (\Exception $e) {
            Log::error('Error in payment notification:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(["error" => "Erreur de notification"], 500);
        }
    }

    public function success()
    {
        Log::info('Payment success page accessed');
        return view('paiement.success');
    }

    // Méthode de test pour l'authentification
    public function testAuth(Request $request)
    {
        Log::info('=== TEST AUTH ===');
        Log::info('Test auth - Headers:', $request->headers->all());
        Log::info('Test auth - User:', (array)auth()->user());
        Log::info('Test auth - Token:', (array)$request->bearerToken());

        $user = auth()->user();

        return response()->json([
            'authenticated' => $user ? true : false,
            'user' => $user,
            'token_present' => $request->bearerToken() ? true : false,
            'headers' => $request->headers->all()
        ]);
    }
}
