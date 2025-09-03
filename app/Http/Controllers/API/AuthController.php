<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur (sans rôle assigné ici).
     */
    public function register(Request $request)
    {
        try {
            // Log des données reçues pour débogage
            Log::info('=== DEBUT INSCRIPTION ===');
            Log::info('Method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('Parsed data:', $request->all());

            // Validation des données
            $data = $request->validate([
                'nom'       => 'required|string|max:255',
                'prenom'    => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email|max:255',
                'password'  => 'required|confirmed|min:6',
                'telephone' => 'nullable|string|max:20',
                'adresse'   => 'nullable|string|max:500',
                'ville'     => 'nullable|string|max:100',
            ]);

            Log::info('Validation réussie pour:', array_keys($data));

            // Hasher le mot de passe
            $data['password'] = Hash::make($data['password']);

            // Supprimer password_confirmation (non nécessaire pour la création)
            unset($data['password_confirmation']);

            // Créer l'utilisateur
            $user = User::create($data);

            // ✅ CORRECTION : Vérifier et créer le rôle avec le bon guard
            $guardName = 'api'; // ou config('auth.defaults.guard') si vous voulez être dynamique

            if (!\Spatie\Permission\Models\Role::where('name', 'Client')
                ->where('guard_name', $guardName)
                ->exists()) {
                \Spatie\Permission\Models\Role::create([
                    'name' => 'Client',
                    'guard_name' => $guardName
                ]);
                Log::info('Rôle "Client" créé automatiquement avec guard: ' . $guardName);
            }

            // ✅ Assigner le rôle avec le bon guard
            $user->assignRole('Client');

            Log::info('Utilisateur créé avec succès:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role_assigned' => 'Client',
                'guard' => $guardName
            ]);

            // Générer un token JWT pour l'utilisateur nouvellement inscrit
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 201,
                'message' => 'Inscription réussie',
                'token' => $token,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                        'nom_complet' => $user->nom_complet
                    ]
                ]
            ], 201)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (ValidationException $e) {
            Log::error('=== ERREUR DE VALIDATION ===');
            Log::error('Erreurs de validation:', ['errors' => $e->errors()]);
            Log::error('Données reçues:', ['data' => $request->all()]);

            return response()->json([
                'status' => 422,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('=== ERREUR BASE DE DONNÉES ===');
            Log::error('Erreur SQL:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // Gestion spécifique des erreurs de contrainte unique
            if ($e->getCode() === '23000') {
                return response()->json([
                    'status' => 409,
                    'message' => 'Un compte avec cet email existe déjà'
                ], 409)
                    ->header('Content-Type', 'application/json; charset=utf-8');
            }

            return response()->json([
                'status' => 500,
                'message' => 'Erreur de base de données'
            ], 500)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            Log::error('=== ERREUR ROLE ===');
            Log::error('Erreur rôle Spatie:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur de configuration des rôles'
            ], 500)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (Exception $e) {
            Log::error('=== ERREUR GÉNÉRALE ===');
            Log::error('Erreur lors de l\'inscription:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Trace masquée en production'
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur lors de l\'inscription'
            ], 500)
                ->header('Content-Type', 'application/json; charset=utf-8');
        }
    }
    public function login(Request $request)
    {
        try {
            // Log des données reçues pour débogage
            Log::info('=== DEBUT CONNEXION ===');
            Log::info('Method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('Email fourni:', ['email' => $request->input('email')]);

            // Validation des données
            $credentials = $request->validate([
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6'
            ]);

            Log::info('Validation réussie pour:', array_keys($credentials));

            // Tentative d'authentification avec JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('Échec d\'authentification:', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'status' => 401,
                    'message' => 'Email ou mot de passe incorrect'
                ], 401)
                    ->header('Content-Type', 'application/json; charset=utf-8');
            }

            // Récupérer l'utilisateur authentifié
            $user = JWTAuth::user();

            Log::info('Connexion réussie:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Connexion réussie',
                'token' => $token,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                        'nom_complet' => $user->nom_complet ?? $user->prenom . ' ' . $user->nom
                    ]
                ]
            ], 200)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (ValidationException $e) {
            Log::error('=== ERREUR DE VALIDATION LOGIN ===');
            Log::error('Erreurs de validation:', ['errors' => $e->errors()]);

            return response()->json([
                'status' => 422,
                'message' => 'Données de connexion invalides',
                'errors' => $e->errors()
            ], 422)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('=== ERREUR JWT ===');
            Log::error('Erreur JWT:', [
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Trace masquée'
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la génération du token'
            ], 500)
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (Exception $e) {
            Log::error('=== ERREUR GÉNÉRALE LOGIN ===');
            Log::error('Erreur lors de la connexion:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Trace masquée'
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur lors de la connexion'
            ], 500)
                ->header('Content-Type', 'application/json; charset=utf-8');
        }
    }
    /**
     * Déconnexion (invalidation du token).
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status'  => 200,
                'message' => 'Déconnexion réussie'
            ], 200);

        } catch (Exception $e) {
            Log::error('Erreur lors de la déconnexion:', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Rafraîchissement du token JWT.
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status' => 200,
                'token'  => $newToken
            ], 200);

        } catch (Exception $e) {
            Log::error('Erreur lors du rafraîchissement:', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 401,
                'message' => 'Token invalide'
            ], 401);
        }
    }

    /**
     * Récupération des données de l'utilisateur authentifié.
     */
    public function me()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Non authentifié'
                ], 401);
            }

            return response()->json([
                'status' => 200,
                'user'   => [
                    'id'       => $user->id,
                    'nom'      => $user->nom,
                    'prenom'   => $user->prenom,
                    'email'    => $user->email,
                    'roles'    => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : []
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du profil:', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }
}
