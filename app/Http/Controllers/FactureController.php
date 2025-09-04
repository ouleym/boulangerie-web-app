<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\User;
use App\Mail\FactureEnvoiMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureController extends Controller
{
    /**
     * Télécharger la facture PDF
     */
    public function telecharger($commandeId)
    {
        $commande = Commande::with(['detailsCommandes.produit', 'user'])->findOrFail($commandeId);

        // Vérifier les permissions avec Spatie
        if (Auth::user()->hasRole('Client') && $commande->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        $user = $commande->user;

        // Générer le PDF
        $pdf = PDF::loadView('factures.pdf', compact('commande', 'user'));

        $fileName = "facture_commande_{$commande->id}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Afficher la facture en ligne
     */
    public function afficher($commandeId)
    {
        $commande = Commande::with(['detailsCommandes.produit', 'user'])->findOrFail($commandeId);

        // Vérifier les permissions avec Spatie
        if (Auth::user()->hasRole('Client') && $commande->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        $user = $commande->user;

        return view('factures.show', compact('commande', 'user'));
    }

    /**
     * Renvoyer la facture par email
     */
    public function renvoyer(Request $request, $commandeId)
    {
        $commande = Commande::with(['detailsCommandes.produit', 'user'])->findOrFail($commandeId);

        // Vérifier les permissions avec Spatie
        if (!Auth::user()->can('send invoices')) {
            abort(403, 'Vous n\'avez pas la permission d\'envoyer des factures');
        }

        $user = $commande->user;
        $email = $request->get('email', $user->email);

        try {
            Mail::to($email)->send(new FactureEnvoiMail($commande, $user));

            return response()->json([
                'success' => true,
                'message' => 'Facture envoyée avec succès à ' . $email
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi facture: ' . $e->getMessage(), [
                'commande_id' => $commande->id,
                'email' => $email
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la facture'
            ], 500);
        }
    }

    /**
     * Liste des factures pour l'admin
     */
    public function index(Request $request)
    {
        // Vérifier les permissions avec Spatie
        if (!Auth::user()->can('view invoices')) {
            abort(403, 'Vous n\'avez pas la permission de voir les factures');
        }

        $query = Commande::with(['user', 'detailsCommandes'])
            ->whereIn('statut', ['livree', 'annulee']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_debut')) {
            $query->where('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('created_at', '<=', $request->date_fin . ' 23:59:59');
        }

        $commandes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.factures.index', compact('commandes'));
    }

    /**
     * Générer et télécharger multiple factures (ZIP)
     */
    public function telechargerMultiples(Request $request)
    {
        // Vérifier les permissions avec Spatie
        if (!Auth::user()->can('download invoices')) {
            abort(403, 'Vous n\'avez pas la permission de télécharger les factures');
        }

        $commandeIds = $request->input('commandes', []);

        if (empty($commandeIds)) {
            return back()->with('error', 'Aucune commande sélectionnée');
        }

        $commandes = Commande::with(['detailsCommandes.produit', 'user'])
            ->whereIn('id', $commandeIds)
            ->get();

        if ($commandes->isEmpty()) {
            return back()->with('error', 'Aucune commande trouvée');
        }

        // Créer un fichier ZIP temporaire
        $zipFileName = 'factures_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = storage_path('app/temp/' . $zipFileName);

        // Créer le dossier temp s'il n'existe pas
        if (!file_exists(dirname($zipFilePath))) {
            mkdir(dirname($zipFilePath), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
            return back()->with('error', 'Impossible de créer le fichier ZIP');
        }

        foreach ($commandes as $commande) {
            $pdf = PDF::loadView('factures.pdf', [
                'commande' => $commande,
                'user' => $commande->user
            ]);

            $pdfFileName = "facture_commande_{$commande->id}.pdf";
            $zip->addFromString($pdfFileName, $pdf->output());
        }

        $zip->close();

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }
}
