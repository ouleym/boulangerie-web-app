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
        Route::get('/', [ChatController::class, 'index']);              // GET /api/chats
        Route::post('/', [ChatController::class, 'store']);             // POST /api/chats
        Route::get('/{chat}', [ChatController::class, 'show']);         // GET /api/chats/{id}
        Route::post('/{chat}/messages', [ChatController::class, 'sendMessage']); // POST /api/chats/{id}/messages
        Route::delete('/{chat}', [ChatController::class, 'destroy']);   // DELETE /api/chats/{id}
    });

});

Route::fallback(function () {
    return response()->json([
        'message' => 'Route non trouvée. Vérifie l’URL.'
    ], 404);
});
