<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\Message;
use App\Models\Conversation;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
use Illuminate\Http\Request;

class MessageController extends Controller
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
     * Liste des messages d’une conversation.
     */
    public function index(string $conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
                           ->with('auteur')
                           ->orderBy('created_at', 'asc')
                           ->get();

        return response()->json($messages);
    }

    /**
     * Envoyer un nouveau message.
     */
    public function store(Request $request, string $conversationId)
    {
        $request->validate([
            'auteur_id' => 'required|exists:users,id',
            'contenu' => 'required|string'
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'auteur_id' => $request->auteur_id,
            'contenu' => $request->contenu
        ]);

        return response()->json($message, 201);
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
