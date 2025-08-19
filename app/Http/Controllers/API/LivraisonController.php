<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\Livraison;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
use Illuminate\Http\Request;

class LivraisonController extends Controller
{
    /**
<<<<<<< HEAD
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
=======
     * Liste des livraisons avec commande et employé.
     */
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
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
