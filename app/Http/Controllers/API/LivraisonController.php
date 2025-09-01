<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivraisonController extends Controller
{
    public function index()
    {
        return response()->json(Livraison::with('commande', 'employe')->get());
    }

    /**
     * Créer une nouvelle livraison.
     */
    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'employe_id' => 'required|exists:users,id',
            'statut' => 'required|in:en_cours,livree'
        ]);

        $livraison = Livraison::create($request->all());
        return response()->json($livraison, 201);
    }

    /**
     * Afficher une livraison.
     */
    public function show(string $id)
    {
        $livraison = Livraison::with('commande', 'employe')->findOrFail($id);
        return response()->json($livraison);
    }

    /**
     * Mettre à jour le statut d’une livraison.
     */
    public function update(Request $request, string $id)
    {
        $livraison = Livraison::findOrFail($id);

        $request->validate([
            'statut' => 'in:en_cours,livree'
        ]);

        $livraison->update($request->all());
        return response()->json($livraison);
    }

    /**
     * Supprimer une livraison.
     */
    public function destroy(string $id)
    {
        Livraison::destroy($id);
        return response()->json(null, 204);
    }
}
