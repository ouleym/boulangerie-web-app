<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware Web (pour les sessions)
        $middleware->web([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
        ]);

        // Middleware API pour JWT (SANS Sanctum)
        $middleware->api([
            // EnsureFrontendRequestsAreStateful::class, // ← SUPPRIMÉ (spécifique à Sanctum)
            SubstituteBindings::class,
        ]);

        // Middleware personnalisés avec alias corrects
        $middleware->alias([
            'auth' => Authenticate::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);

        // Désactiver la vérification CSRF pour les routes API
        $middleware->validateCsrfTokens(except: [
            'api/*',
            // 'sanctum/*', // ← SUPPRIMÉ (pas nécessaire pour JWT)
        ]);

        // Middleware global pour CORS
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gestion des erreurs pour les API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Non authentifié',
                    'error' => 'Unauthenticated'
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Accès refusé',
                    'error' => 'Unauthorized'
                ], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Ressource non trouvée',
                    'error' => 'Not Found'
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Méthode non autorisée',
                    'error' => 'Method Not Allowed',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? []
                ], 405);
            }
        });

        // Gestion des erreurs générales
        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // En développement, montrer plus de détails
                if (config('app.debug')) {
                    return response()->json([
                        'message' => 'Erreur serveur',
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ], 500);
                } else {
                    return response()->json([
                        'message' => 'Erreur serveur',
                        'error' => 'Internal Server Error'
                    ], 500);
                }
            }
        });
    })
    ->create();
