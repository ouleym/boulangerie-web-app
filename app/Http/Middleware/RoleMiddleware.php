<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié',
                    'error' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur a l'un des rôles requis
        // Spatie hasRole() peut accepter un array ou des arguments multiples
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Si aucun rôle ne correspond, refuser l'accès
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Accès refusé. Rôle insuffisant.',
                'error' => 'Unauthorized',
                'required_roles' => $roles,
                'user_roles' => $user->getRoleNames()
            ], 403);
        }

        // Pour les routes web, on peut rediriger ou afficher une erreur
        abort(403, 'Accès refusé. Rôle insuffisant.');
    }
}
