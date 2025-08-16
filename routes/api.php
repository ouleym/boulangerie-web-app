<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\API\ProduitController;
use App\Http\Controllers\API\PromotionController;
use App\Http\Controllers\API\CommandeController;
use App\Http\Controllers\API\LivraisonController;
use App\Http\Controllers\API\FactureController;
use App\Http\Controllers\API\ConversationController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes d'authentification (publiques)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Routes d'authentification (utilisateur connecté)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // Ressources API avec toutes les routes CRUD
    Route::apiResource('categories', CategorieController::class);
    Route::apiResource('produits', ProduitController::class);
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('commandes', CommandeController::class);
    Route::apiResource('livraisons', LivraisonController::class);
    Route::apiResource('factures', FactureController::class);
    Route::apiResource('conversations', ConversationController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('notifications', NotificationController::class);

    // Routes spéciales pour les commandes
    Route::prefix('commandes/{commande}')->group(function () {
        Route::patch('/status', [CommandeController::class, 'updateStatus']);
        Route::post('/cancel', [CommandeController::class, 'cancel']);
        Route::get('/tracking', [CommandeController::class, 'tracking']);
    });

    // Routes spéciales pour les produits
    Route::prefix('produits/{produit}')->group(function () {
        Route::post('/toggle-favorite', [ProduitController::class, 'toggleFavorite']);
        Route::get('/reviews', [ProduitController::class, 'reviews']);
        Route::post('/reviews', [ProduitController::class, 'addReview']);
    });

    // Routes spéciales pour les promotions
    Route::prefix('promotions')->group(function () {
        Route::get('/active', [PromotionController::class, 'active']);
        Route::post('/{promotion}/apply', [PromotionController::class, 'apply']);
    });

    // Routes spéciales pour les conversations
    Route::prefix('conversations/{conversation}')->group(function () {
        Route::post('/messages', [ConversationController::class, 'addMessage']);
        Route::patch('/close', [ConversationController::class, 'close']);
        Route::patch('/reopen', [ConversationController::class, 'reopen']);
    });

    // Routes spéciales pour les livraisons
    Route::prefix('livraisons')->group(function () {
        Route::get('/tracking/{trackingNumber}', [LivraisonController::class, 'trackByNumber']);
        Route::patch('/{livraison}/status', [LivraisonController::class, 'updateStatus']);
    });

    // Routes spéciales pour les factures
    Route::prefix('factures')->group(function () {
        Route::get('/{facture}/download', [FactureController::class, 'download']);
        Route::post('/{facture}/send-email', [FactureController::class, 'sendByEmail']);
    });

    // Routes spéciales pour les notifications
    Route::prefix('notifications')->group(function () {
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    });

    // Routes du dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
        Route::get('/profile-completion', [DashboardController::class, 'profileCompletion']);
    });

    // Routes spéciales selon les rôles
    Route::middleware('role:admin,super-admin')->group(function () {
        // Dashboard admin
        Route::prefix('admin/dashboard')->group(function () {
            Route::get('/overview', [DashboardController::class, 'adminOverview']);
            Route::get('/sales-stats', [DashboardController::class, 'salesStats']);
            Route::get('/user-stats', [DashboardController::class, 'userStats']);
            Route::get('/product-stats', [DashboardController::class, 'productStats']);
        });

        // Gestion des utilisateurs (admin uniquement)
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AuthController::class, 'listUsers']);
            Route::post('/users', [AuthController::class, 'createUser']);
            Route::put('/users/{user}', [AuthController::class, 'updateUser']);
            Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
            Route::patch('/users/{user}/toggle-status', [AuthController::class, 'toggleUserStatus']);
        });
    });

    Route::middleware('role:employe,admin,super-admin')->group(function () {
        // Dashboard employé
        Route::prefix('employee/dashboard')->group(function () {
            Route::get('/assigned-orders', [DashboardController::class, 'assignedOrders']);
            Route::get('/pending-tasks', [DashboardController::class, 'pendingTasks']);
        });
    });
});

// Routes publiques (sans authentification)
Route::prefix('public')->group(function () {
    // Produits publics
    Route::get('/produits', [ProduitController::class, 'publicIndex']);
    Route::get('/produits/{produit}', [ProduitController::class, 'publicShow']);
    Route::get('/produits/category/{category}', [ProduitController::class, 'byCategory']);

    // Catégories publiques
    Route::get('/categories', [CategorieController::class, 'publicIndex']);

    // Promotions actives
    Route::get('/promotions/active', [PromotionController::class, 'activePromotions']);
});

// Route de fallback pour API
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint non trouvé'
    ], 404);
});
