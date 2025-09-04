<?php

namespace App\Http\Controllers;

use App\Mail\PromotionMail;
use App\Mail\CommandeConfirmationMail;
use App\Mail\LivraisonMail;
use App\Mail\FactureEnvoiMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function sendPromotionEmail(Request $request)
    {
        try {
            $request->validate([
                'promotion_id' => 'required|integer',
                'email' => 'required|email',
                'customer_name' => 'nullable|string'
            ]);

            // Récupérer la promotion
            $promotion = DB::table('promotions')
                ->where('id', $request->promotion_id)
                ->first();

            if (!$promotion) {
                return response()->json(['error' => 'Promotion non trouvée'], 404);
            }

            // Récupérer les produits liés à cette promotion
            $products = DB::table('produit_promotion')
                ->join('produits', 'produit_promotion.produit_id', '=', 'produits.id')
                ->where('produit_promotion.promotion_id', $request->promotion_id)
                ->select('produits.*')
                ->get();

            // Convertir en array pour le mail
            $promotionData = (array) $promotion;
            $productsData = $products->toArray();

            Mail::to($request->email)->send(new PromotionMail(
                $promotionData,
                $productsData,
                $request->customer_name ?? ''
            ));

            return response()->json(['message' => 'Email de promotion envoyé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur envoi email promotion: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email'], 500);
        }
    }

    public function sendCommandeConfirmation(Request $request)
    {
        try {
            $request->validate([
                'commande_id' => 'required|integer',
                'email' => 'required|email',
                'customer_name' => 'nullable|string'
            ]);

            // Récupérer la commande
            $commande = DB::table('commandes')
                ->where('id', $request->commande_id)
                ->first();

            if (!$commande) {
                return response()->json(['error' => 'Commande non trouvée'], 404);
            }

            // Récupérer les détails de la commande
            $details = DB::table('details_commandes')
                ->join('produits', 'details_commandes.produit_id', '=', 'produits.id')
                ->where('details_commandes.commande_id', $request->commande_id)
                ->select(
                    'details_commandes.*',
                    'produits.nom as produit_nom'
                )
                ->get();

            $commandeData = (array) $commande;
            $detailsData = $details->toArray();
            $total = $commande->montant_total;

            Mail::to($request->email)->send(new CommandeConfirmationMail(
                $commandeData,
                $detailsData,
                $request->customer_name ?? '',
                $total
            ));

            return response()->json(['message' => 'Email de confirmation de commande envoyé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur envoi email commande: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email'], 500);
        }
    }

    public function sendLivraisonEmail(Request $request)
    {
        try {
            $request->validate([
                'livraison_id' => 'required|integer',
                'email' => 'required|email',
                'customer_name' => 'nullable|string'
            ]);

            // Récupérer la livraison et la commande associée
            $livraison = DB::table('livraisons')
                ->where('id', $request->livraison_id)
                ->first();

            if (!$livraison) {
                return response()->json(['error' => 'Livraison non trouvée'], 404);
            }

            $commande = DB::table('commandes')
                ->where('id', $livraison->commande_id)
                ->first();

            $livraisonData = (array) $livraison;
            $commandeData = $commande ? (array) $commande : [];

            Mail::to($request->email)->send(new LivraisonMail(
                $livraisonData,
                $commandeData,
                $request->customer_name ?? ''
            ));

            return response()->json(['message' => 'Email de livraison envoyé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur envoi email livraison: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email'], 500);
        }
    }

    public function sendFactureEmail(Request $request)
    {
        try {
            $request->validate([
                'commande_id' => 'required|integer',
                'email' => 'required|email',
                'customer_name' => 'nullable|string',
                'pdf_path' => 'nullable|string'
            ]);

            $commande = DB::table('commandes')
                ->where('id', $request->commande_id)
                ->first();

            if (!$commande) {
                return response()->json(['error' => 'Commande non trouvée'], 404);
            }

            $commandeData = (array) $commande;
            $factureData = [
                'numero' => 'FACT-' . $request->commande_id . '-' . date('Y'),
                'date' => date('Y-m-d'),
                'montant' => $commande->montant_total
            ];

            Mail::to($request->email)->send(new FactureEnvoiMail(
                $factureData,
                $commandeData,
                $request->customer_name ?? '',
                $request->pdf_path
            ));

            return response()->json(['message' => 'Email de facture envoyé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur envoi email facture: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email'], 500);
        }
    }

    public function getEmailHistory(Request $request)
    {
        try {
            // Cette méthode pourrait retourner l'historique des emails envoyés
            // Vous pourriez créer une table mail_logs pour tracker cela

            return response()->json([
                'message' => 'Historique des emails',
                'data' => []
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération de l\'historique'], 500);
        }
    }

    public function testEmailConfiguration()
    {
        try {
            Mail::raw('Test de configuration email', function ($message) {
                $message->to('test@example.com')
                    ->subject('Test Email Configuration');
            });

            return response()->json(['message' => 'Configuration email OK - Vérifiez les logs']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur configuration: ' . $e->getMessage()], 500);
        }
    }
}
