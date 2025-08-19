<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< HEAD
=======
use App\Models\Categorie;
use Illuminate\Support\Facades\Validator;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)

class CategorieController extends Controller
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
     * Liste des catégories (protégée).
     */
    public function index()
    {
        $categories = Categorie::all();
        return response()->json($categories, 200);
    }

    /**
     * Liste des catégories (publique).
     */
    public function publicIndex()
    {
        $categories = Categorie::where('active', true)->get();
        return response()->json($categories, 200);
    }

    /**
     * Créer une nouvelle catégorie.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:categories,nom',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $categorie = Categorie::create($request->only(['nom', 'description', 'active']));

        return response()->json([
            'message' => 'Catégorie créée avec succès',
            'data' => $categorie
        ], 201);
    }

    /**
     * Afficher une catégorie spécifique.
     */
    public function show($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        return response()->json($categorie, 200);
    }

    /**
     * Mettre à jour une catégorie.
     */
    public function update(Request $request, $id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255|unique:categories,nom,' . $id,
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $categorie->update($request->only(['nom', 'description', 'active']));

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $categorie
        ], 200);
    }

    /**
     * Supprimer une catégorie.
     */
    public function destroy($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        $categorie->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès'
        ], 200);
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
