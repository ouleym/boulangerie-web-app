<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
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
    }
}
