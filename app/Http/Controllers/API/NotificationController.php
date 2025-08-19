<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\Notification;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
use Illuminate\Http\Request;

class NotificationController extends Controller
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
     * Liste des notifications avec leur utilisateur.
     */
    public function index()
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
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
