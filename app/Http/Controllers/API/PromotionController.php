<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\Promotion;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
use Illuminate\Http\Request;

class PromotionController extends Controller
{
<<<<<<< HEAD
    /**
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
    // Liste des promotions avec leurs produits
    public function index()
    {
        return response()->json(Promotion::with('produits')->get());
    }

    // Créer une nouvelle promotion et l’associer à des produits
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pourcentage,montant_fixe',
            'valeur' => 'required|numeric',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'produits' => 'array' // optionnel
        ]);

        $promotion = Promotion::create($request->all());

        if ($request->has('produits')) {
            $promotion->produits()->sync($request->produits);
        }

        return response()->json($promotion, 201);
    }

    // Afficher une promotion
    public function show(string $id)
    {
        $promotion = Promotion::with('produits')->findOrFail($id);
        return response()->json($promotion);
    }

    // Mettre à jour une promotion
    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update($request->all());

        if ($request->has('produits')) {
            $promotion->produits()->sync($request->produits);
        }

        return response()->json($promotion);
    }

    // Supprimer une promotion
    public function destroy(string $id)
    {
        Promotion::destroy($id);
        return response()->json(null, 204);
    }

    // Appliquer les promotions actives uniquement sur les produits
    public function applyActivePromotions()
    {
        $promotions = Promotion::where('active', true)
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->with('produits')
            ->get();

        foreach ($promotions as $promo) {
            foreach ($promo->produits as $produit) {
                $original = $produit->prix_original ?? $produit->prix;
                $produit->prix = $promo->type === 'pourcentage'
                    ? $original * (1 - $promo->valeur / 100)
                    : max(0, $original - $promo->valeur);
                $produit->save();
            }
        }

        return response()->json(['message' => 'Promotions appliquées aux produits']);
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
