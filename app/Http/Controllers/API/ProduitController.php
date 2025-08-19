<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< HEAD
=======
use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)

class ProduitController extends Controller
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
     * Liste des produits (protégée).
     */
    public function index()
    {
        $produits = Produit::with('categorie')->get();
        return response()->json($produits, 200);
    }

    /**
     * Liste des produits (publique).
     */
    public function publicIndex()
    {
        $produits = Produit::where('active', true)->with('categorie')->get();
        return response()->json($produits, 200);
    }

    /**
     * Afficher un produit public.
     */
    public function publicShow($id)
    {
        $produit = Produit::where('id', $id)->where('active', true)->with('categorie')->first();

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        return response()->json($produit, 200);
    }

    /**
     * Produits par catégorie.
     */
    public function byCategory($categoryId)
    {
        $categorie = Categorie::find($categoryId);

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $produits = Produit::where('categorie_id', $categorie->id)->where('active', true)->get();

        return response()->json($produits, 200);
    }

    /**
     * Créer un produit.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'categorie_id' => 'required|exists:categories,id',
            'stock' => 'integer|min:0',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $produit = Produit::create($request->only(['nom', 'description', 'prix', 'categorie_id', 'stock', 'active']));

        return response()->json([
            'message' => 'Produit créé avec succès',
            'data' => $produit
        ], 201);
    }

    /**
     * Afficher un produit (protégé).
     */
    public function show($id)
    {
        $produit = Produit::with('categorie')->find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        return response()->json($produit, 200);
    }

    /**
     * Mettre à jour un produit.
     */
    public function update(Request $request, $id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'sometimes|numeric|min:0',
            'categorie_id' => 'sometimes|exists:categories,id',
            'stock' => 'integer|min:0',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $produit->update($request->only(['nom', 'description', 'prix', 'categorie_id', 'stock', 'active']));

        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'data' => $produit
        ], 200);
    }

    /**
     * Supprimer un produit.
     */
    public function destroy($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $produit->delete();

        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }

    /**
     * Ajouter/enlever un produit des favoris.
     */
    public function toggleFavorite($id)
    {
        $user = auth()->user();
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        if ($user->favorites()->where('produit_id', $id)->exists()) {
            $user->favorites()->detach($id);
            return response()->json(['message' => 'Produit retiré des favoris'], 200);
        } else {
            $user->favorites()->attach($id);
            return response()->json(['message' => 'Produit ajouté aux favoris'], 200);
        }
    }

    /**
     * Liste des avis d’un produit.
     */
    public function reviews($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $reviews = $produit->reviews()->with('user')->get();

        return response()->json($reviews, 200);
    }

    /**
     * Ajouter un avis sur un produit.
     */
    public function addReview(Request $request, $id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = Review::create([
            'produit_id' => $produit->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'commentaire' => $request->commentaire,
        ]);

        return response()->json([
            'message' => 'Avis ajouté avec succès',
            'data' => $review
        ], 201);
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
