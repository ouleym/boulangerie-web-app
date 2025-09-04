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
    public function initier(Request $request, CinetpayService $cinetpay)
    {
        try {
            Log::info('=== DÉBUT INITIALISATION PAIEMENT ===', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'montant' => 'required|numeric|min:100'
            ]);

            if ($validator->fails()) {
                Log::error('Validation échouée:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'error' => 'Données invalides',
                    'messages' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            if (!$user) {
                Log::error('Utilisateur non authentifié');
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            $transactionId = "PAY-" . time() . "-" . $user->id;

            Log::info('Données utilisateur:', [
                'user_id' => $user->id,
                'user_name' => $user->nom ?? 'N/A',
                'user_email' => $user->email ?? 'N/A',
                'transaction_id' => $transactionId,
                'montant' => $request->montant
            ]);

            // Appel CinetPay
            $result = $cinetpay->initierPaiement(
                $request->montant,
                $transactionId,
                "Paiement commande Boulangerie Hi-Tech",
                config('app.url') . '/payment/success',
                config('app.url') . '/api/payment/notify'
            );

            Log::info('Résultat CinetPay:', $result);

            // ✅ Vérification plus robuste de la réponse
            if (!isset($result['data']) || !isset($result['data']['payment_url'])) {
                Log::error('Structure de réponse CinetPay invalide:', $result);
                throw new \Exception('Réponse CinetPay invalide - URL de paiement manquante');
            }

            // Enregistrement en base avec gestion d'erreur
            try {
                $payement = Payement::create([
                    'user_id'        => $user->id,
                    'montant'        => $request->montant,
                    'transaction_ref'=> $transactionId,
                    'statut'         => 'en_attente',  // ✅ Ajouté pour cohérence
                    'methode'        => 'CinetPay',
                    'devise'         => 'XOF',
                    'payment_token'  => $result['data']['payment_token'] ?? null,
                    'cinetpay_data'  => $result  // ✅ Stockage de la réponse complète
                ]);

                Log::info('Paiement créé en base:', ['id' => $payement->id]);

            } catch (\Exception $dbError) {
                Log::error('Erreur création paiement en base:', [
                    'error' => $dbError->getMessage(),
                    'file' => $dbError->getFile(),
                    'line' => $dbError->getLine()
                ]);
                throw new \Exception('Erreur de sauvegarde: ' . $dbError->getMessage());
            }

            $response = [
                'success' => true,
                'data' => [
                    'transaction_id' => $transactionId,
                    'payment_url' => $result['data']['payment_url'],
                    'payment_token' => $result['data']['payment_token'] ?? null,
                    'amount' => $request->montant,
                    'currency' => 'XOF'
                ],
                'message' => 'Paiement initialisé avec succès'
            ];

            Log::info('=== SUCCÈS INITIALISATION PAIEMENT ===', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('=== ERREUR INITIALISATION PAIEMENT ===', [
                'user_id' => auth()->id(),
                'montant' => $request->montant ?? 'N/A',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'initialisation du paiement',
                'message' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur',
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
            Log::info('=== NOTIFICATION CINETPAY REÇUE ===', $request->all());

            $transactionId = $request->transaction_id;
            $status = $request->status;

            if (!$transactionId) {
                Log::error('transaction_id manquant dans la notification');
                return response()->json(["error" => "transaction_id manquant"], 400);
            }

            $payement = Payement::where('transaction_ref', $transactionId)->first();

            if (!$payement) {
                Log::error('Paiement introuvable:', ['transaction_id' => $transactionId]);
                return response()->json(["message" => "ok"]); // Répondre ok même si pas trouvé
            }

            $newStatus = match($status) {
                'ACCEPTED' => 'success',
                'REFUSED', 'CANCELLED' => 'failed',
                default => 'pending'
            };

            $payement->update([
                'status' => $newStatus,
                'statut' => $newStatus == "success" ? "valide" : "echoue",
                'date_paiement' => now()
            ]);

            Log::info('Paiement mis à jour:', [
                'transaction_id' => $transactionId,
                'new_status' => $newStatus
            ]);

            return response()->json(["message" => "ok"]);

        } catch (\Exception $e) {
            Log::error('Erreur notification:', ['error' => $e->getMessage()]);
            return response()->json(["message" => "ok"]); // Toujours répondre ok à CinetPay
        }
    }
}
