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
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',   // routes web classiques
        api: __DIR__.'/../routes/api.php',   // routes API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware global pour les routes "web"
        $middleware->web([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            EnsureFrontendRequestsAreStateful::class, // clé pour Sanctum SPA
        ]);

        // Middleware global pour les routes "api"
        $middleware->api([
            SubstituteBindings::class,
        ]);

        // Middleware par alias (réutilisables dans tes routes)
        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.sanctum' => EnsureFrontendRequestsAreStateful::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
            }
        });
    })
    ->create();
