<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Livraison;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LivraisonController extends Controller
{
    /**
     * Liste des livraisons
     */
    public function index(Request $request)
    {
        $query = Livraison::with(['commande.user', 'commande.detailsCommandes.produit']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('commande', function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $livraisons = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques rapides
        $stats = [
            'en_attente' => Livraison::where('statut', 'en_attente')->count(),
            'confirmee' => Livraison::where('statut', 'confirmee')->count(),
            'en_preparation' => Livraison::where('statut', 'en_preparation')->count(),
            'en_livraison' => Livraison::where('statut', 'en_livraison')->count(),
            'livree' => Livraison::where('statut', 'livree')->count(),
        ];

        return view('admin.livraisons.index', compact('livraisons', 'stats'));
    }

    /**
     * Afficher une livraison
     */
    public function show(Livraison $livraison)
    {
        $livraison->load(['commande.user', 'commande.detailsCommandes.produit']);

        return view('admin.livraisons.show', compact('livraison'));
    }

    /**
     * Mettre à jour le statut d'une livraison
     */
    public function updateStatus(Request $request, Livraison $livraison)
    {
        $request->validate([
            'statut' => [
                'required',
                Rule::in(['en_attente', 'confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree', 'annulee'])
            ],
            'notes' => 'nullable|string|max:500'
        ]);

        $ancienStatut = $livraison->statut;
        $nouveauStatut = $request->statut;

        DB::beginTransaction();

        try {
            // Mettre à jour la livraison
            $livraison->update([
                'statut' => $nouveauStatut
            ]);

            // Mettre à jour le statut de la commande correspondant
            $statutCommande = $this->getStatutCommandeFromLivraison($nouveauStatut);
            $livraison->commande->update([
                'statut' => $statutCommande
            ]);

            // Ajouter une note si fournie
            if ($request->filled('notes')) {
                $livraison->commande->update([
                    'notes' => $livraison->commande->notes . "\n[" . now()->format('d/m/Y H:i') . "] " . $request->notes
                ]);
            }

            // Mettre à jour l'heure de départ si le statut passe à "en_livraison"
            if ($nouveauStatut === 'en_livraison' && $ancienStatut !== 'en_livraison') {
                $livraison->update([
                    'heure_depart' => now()
                ]);
            }

            DB::commit();

            // L'observer se chargera d'envoyer les notifications

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Statut mis à jour avec succès',
                    'nouveau_statut' => $nouveauStatut,
                    'statut_libelle' => ucfirst(str_replace('_', ' ', $nouveauStatut))
                ]);
            }

            return redirect()->back()->with('success', 'Statut de livraison mis à jour avec succès');

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Erreur mise à jour statut livraison: ' . $e->getMessage(), [
                'livraison_id' => $livraison->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du statut'
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du statut');
        }
    }

    /**
     * Mise à jour en lot des statuts
     */
    public function updateMultipleStatus(Request $request)
    {
        $request->validate([
            'livraisons' => 'required|array',
            'livraisons.*' => 'exists:livraisons,id',
            'statut' => [
                'required',
                Rule::in(['en_attente', 'confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree', 'annulee'])
            ]
        ]);

        $livraisonIds = $request->livraisons;
        $nouveauStatut = $request->statut;

        DB::beginTransaction();

        try {
            $livraisons = Livraison::whereIn('id', $livraisonIds)->get();

            foreach ($livraisons as $livraison) {
                $ancienStatut = $livraison->statut;

                // Mettre à jour la livraison
                $livraison->update(['statut' => $nouveauStatut]);

                // Mettre à jour la commande
                $statutCommande = $this->getStatutCommandeFromLivraison($nouveauStatut);
                $livraison->commande->update(['statut' => $statutCommande]);

                // Mettre à jour l'heure de départ si nécessaire
                if ($nouveauStatut === 'en_livraison' && $ancienStatut !== 'en_livraison') {
                    $livraison->update(['heure_depart' => now()]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($livraisonIds) . ' livraisons mises à jour avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Erreur mise à jour multiple statuts: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des statuts'
            ], 500);
        }
    }

    /**
     * Obtenir le statut de commande correspondant au statut de livraison
     */
    private function getStatutCommandeFromLivraison($statutLivraison)
    {
        $mapping = [
            'en_attente' => 'en_attente',
            'confirmee' => 'confirmee',
            'en_preparation' => 'en_preparation',
            'prete' => 'prete',
            'en_livraison' => 'en_livraison',
            'livree' => 'livree',
            'annulee' => 'annulee'
        ];

        return $mapping[$statutLivraison] ?? 'en_attente';
    }

    /**
     * Tableau de bord des livraisons du jour
     */
    public function dashboard()
    {
        $aujourd_hui = now()->toDateString();

        $livraisons = Livraison::with(['commande.user', 'commande.detailsCommandes.produit'])
            ->whereDate('created_at', $aujourd_hui)
            ->orderBy('statut')
            ->orderBy('created_at')
            ->get();

        $statistiques = [
            'total_aujourd_hui' => $livraisons->count(),
            'en_attente' => $livraisons->where('statut', 'en_attente')->count(),
            'en_preparation' => $livraisons->where('statut', 'en_preparation')->count(),
            'en_livraison' => $livraisons->where('statut', 'en_livraison')->count(),
            'livrees' => $livraisons->where('statut', 'livree')->count(),
            'revenus_jour' => $livraisons->where('statut', 'livree')
                ->sum(function($livraison) {
                    return $livraison->commande->montant_total;
                })
        ];

        return view('admin.livraisons.dashboard', compact('livraisons', 'statistiques'));
    }
}
