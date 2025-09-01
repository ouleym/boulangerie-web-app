<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * ✅ Middleware pour vérifier les rôles utilisateur
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Non authentifié'
            ], 401);
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur a au moins un des rôles requis
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Si aucun rôle ne correspond
        return response()->json([
            'status' => 403,
            'message' => 'Accès refusé. Rôle insuffisant.',
            'required_roles' => $roles,
            'user_roles' => $user->getRoleNames()
        ], 403);
    }
}

// ✅ Middleware pour les permissions spécifiques
class PermissionMiddleware
{
    /**
     * Middleware pour vérifier les permissions utilisateur
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Non authentifié'
            ], 401);
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur a au moins une des permissions requises
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 403,
            'message' => 'Accès refusé. Permission insuffisante.',
            'required_permissions' => $permissions,
            'user_permissions' => $user->getAllPermissions()->pluck('name')
        ], 403);
    }
}
