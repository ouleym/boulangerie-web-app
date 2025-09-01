<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\DetailsCommande;
use App\Models\Produit;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function index()
    {
        return response()->json(Commande::with('client', 'produits', 'livraison', 'facture')->get());
    }

    /**
     * Créer une nouvelle commande (calcul du total automatiquement).
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'mode_paiement' => 'required|in:en_ligne,a_la_livraison',
            'produits' => 'required|array',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
        ]);

        $total = 0;

        // Calcul du total
        foreach ($request->produits as $prod) {
            $produit = Produit::findOrFail($prod['id']);
            $total += $produit->prix * $prod['quantite'];
        }

        $commande = Commande::create([
            'client_id' => $request->client_id,
            'mode_paiement' => $request->mode_paiement,
            'statut' => 'en_preparation',
            'total' => $total,
        ]);

        // Sauvegarder les détails
        foreach ($request->produits as $prod) {
            DetailsCommande::create([
                'commande_id' => $commande->id,
                'produit_id' => $prod['id'],
                'quantite' => $prod['quantite']
            ]);
        }

        return response()->json($commande->load('produits'), 201);
    }

    /**
     * Afficher une commande spécifique.
     */
    public function show(string $id)
    {
        $commande = Commande::with('client', 'produits', 'livraison', 'facture')->findOrFail($id);
        return response()->json($commande);
    }

    /**
     * Mettre à jour une commande (ex: statut).
     */
    public function update(Request $request, string $id)
    {
        $commande = Commande::findOrFail($id);

        $request->validate([
            'statut' => 'in:en_preparation,prete,en_livraison,livree'
        ]);

        $commande->update($request->all());
        return response()->json($commande);
    }

    /**
     * Supprimer une commande.
     */
    public function destroy(string $id)
    {
        Commande::destroy($id);
        return response()->json(null, 204);
    }
}
