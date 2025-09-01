<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur (sans rôle assigné ici).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string',
            'prenom'    => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|confirmed|min:6',
            'telephone' => 'nullable|string',
            'adresse'   => 'nullable|string',
            'ville'     => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'status' => 201,
            'data'   => $user
        ], 201);
    }

    /**
     * Connexion avec génération du token contenant les rôles.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // Tentative d'authentification
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status'  => 401,
                'token'   => null,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 200,
            'token'  => $token,
            'user'   => [
                'id'       => $user->id,
                'nom'      => $user->nom,
                'prenom'   => $user->prenom,
                'email'    => $user->email,
                'roles'    => $user->getRoleNames()
            ]
        ], 200);
    }

    /**
     * Déconnexion (invalidation du token).
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status'  => 200,
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    /**
     * Rafraîchissement du token JWT.
     */
    public function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'status' => 200,
            'token'  => $newToken
        ], 200);
    }

    /**
     * Récupération des données de l'utilisateur authentifié.
     */
    public function me()
    {
        $user = Auth::user();

        return response()->json([
            'status' => 200,
            'user'   => [
                'id'       => $user->id,
                'nom'      => $user->nom,
                'prenom'   => $user->prenom,
                'email'    => $user->email,
                'roles'    => $user->getRoleNames()
            ]
        ], 200);
    }
}
