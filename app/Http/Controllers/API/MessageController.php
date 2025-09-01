<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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
    }
}
