<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AuthController,
    CategorieController,
    CommandeController,
    DashboardController,
    FactureController,
    LivraisonController,
    NotificationController,
    ProduitController,
    PromotionController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ Routes publiques (pas besoin de token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Routes protégées par JWT
Route::middleware(['auth:api'])->group(function () {
    // Routes de test (à supprimer en production)
    Route::post('/test-auth', [\App\Http\Controllers\API\PaiementController::class, 'testAuth']);
    Route::get('/test-cinetpay', function() {
        $service = new \App\Services\CinetpayService();
        return response()->json($service->testConfiguration());
    });

    // Route de paiement
    Route::post('/payment/init', [\App\Http\Controllers\API\PaiementController::class, 'initier']);

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // API Resources pour tes contrôleurs
    Route::apiResources([
        'categories'     => CategorieController::class,
        'commandes'      => CommandeController::class,
        'dashboard'      => DashboardController::class,
        'factures'       => FactureController::class,
        'livraisons'     => LivraisonController::class,
        'notifications'  => NotificationController::class,
        'produits'       => ProduitController::class,
        'promotions'     => PromotionController::class,
    ]);

    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{chat}', [ChatController::class, 'show']);
        Route::post('/{chat}/messages', [ChatController::class, 'sendMessage']);
        Route::delete('/{chat}', [ChatController::class, 'destroy']);
    });
});

// Routes publiques pour les callbacks CinetPay
Route::post('/payment/notify', [\App\Http\Controllers\API\PaiementController::class, 'notify']);

Route::fallback(function () {
    return response()->json([
        'message' => 'Route non trouvée. Vérifie l\'URL.'
    ], 404);
});
