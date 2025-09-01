<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{ public function index()
{
    return response()->json(Notification::with('user')->get());
}

    /**
     * Créer une nouvelle notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'contenu' => 'required|string',
            'statut' => 'required|in:non_lu,lu'
        ]);

        $notification = Notification::create($request->all());
        return response()->json($notification, 201);
    }

    /**
     * Afficher une notification.
     */
    public function show(string $id)
    {
        $notification = Notification::with('user')->findOrFail($id);
        return response()->json($notification);
    }

    /**
     * Mettre à jour une notification.
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update($request->all());
        return response()->json($notification);
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(string $id)
    {
        Notification::destroy($id);
        return response()->json(null, 204);
    }
}
