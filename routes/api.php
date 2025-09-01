<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AuthController,
    CategorieController,
    CommandeController,
    ConversationController,
    DashboardController,
    FactureController,
    LivraisonController,
    MessageController,
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
        'conversations'  => ConversationController::class,
        'dashboard'      => DashboardController::class,
        'factures'       => FactureController::class,
        'livraisons'     => LivraisonController::class,
        'messages'       => MessageController::class,
        'notifications'  => NotificationController::class,
        'produits'       => ProduitController::class,
        'promotions'     => PromotionController::class,
    ]);

});

// ✅ Route de test publique (ex: healthcheck API)
Route::get('/ping', function () {
    return response()->json(['status' => 'API is working!']);
});

// ✅ Fallback pour 404
Route::fallback(function () {
    return response()->json([
        'message' => 'Route non trouvée. Vérifie l’URL.'
    ], 404);
});
