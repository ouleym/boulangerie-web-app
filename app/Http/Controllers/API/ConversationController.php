<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\Conversation;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
use Illuminate\Http\Request;

class ConversationController extends Controller
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
     * Liste des conversations (admin/employé voit tout, client voit les siennes).
     */
    public function index(Request $request)
    {
        if ($request->user()->role === 'client') {
            $conversations = Conversation::where('client_id', $request->user()->id)->with('messages')->get();
        } else {
            $conversations = Conversation::with('client', 'messages')->get();
        }

        return response()->json($conversations);
    }

    /**
     * Créer une nouvelle conversation (démarrée par un client).
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'sujet' => 'required|string|max:255'
        ]);

        $conversation = Conversation::create([
            'client_id' => $request->client_id,
            'sujet' => $request->sujet,
        ]);

        return response()->json($conversation, 201);
    }

    /**
     * Afficher une conversation avec ses messages.
     */
    public function show(string $id)
    {
        $conversation = Conversation::with('client', 'messages')->findOrFail($id);
        return response()->json($conversation);
    }

    /**
     * Supprimer une conversation (admin uniquement).
     */
    public function destroy(string $id)
    {
        Conversation::destroy($id);
        return response()->json(null, 204);
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
