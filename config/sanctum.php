<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(path: __DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',   // obligatoire pour Sanctum
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(callback: function (Middleware $middleware): void {
        // Middleware global web (sessions, cookies)
        $middleware->web(append: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // Middleware global API
        $middleware->api(append: [
            SubstituteBindings::class,
        ]);

        // Middleware par alias
        $middleware->alias(aliases: [
            'auth' => Authenticate::class,
            'auth:sanctum' => EnsureFrontendRequestsAreStateful::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): Exceptions {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
            }
        });

        return $exceptions;
    })

    ->create();
