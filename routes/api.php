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
|
| Toutes les routes API et Web fusionnées dans un seul fichier.
| Routes API protégées par Sanctum et routes Web avec sessions.
|
*/

// ==========================================
// Routes d'authentification publiques
// ==========================================

// Auth API (Sanctum)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Auth Web (Sessions) - Middleware web
Route::middleware(['web'])->group(function () {
    Route::post('/web/login', [AuthController::class, 'login']);
    Route::post('/web/logout', [AuthController::class, 'logout']);
});

// ==========================================
// Routes publiques API
// ==========================================

Route::prefix('public')->group(function () {
    Route::get('/produits', [ProduitController::class, 'publicIndex']);
    Route::get('/produits/{produit}', [ProduitController::class, 'publicShow']);
    Route::get('/produits/category/{category}', [ProduitController::class, 'byCategory']);
    Route::get('/categories', [CategorieController::class, 'publicIndex']);
    Route::get('/promotions/active', [PromotionController::class, 'activePromotions']);
});

// ==========================================
// Routes protégées Sanctum (API)
// ==========================================

Route::middleware(['auth:sanctum'])->group(function () {

    // Auth utilisateur connecté
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // Ressources API CRUD
    Route::apiResource('categories', CategorieController::class);
    Route::apiResource('produits', ProduitController::class);
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('commandes', CommandeController::class);
    Route::apiResource('livraisons', LivraisonController::class);
    Route::apiResource('factures', FactureController::class);
    Route::apiResource('conversations', ConversationController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('notifications', NotificationController::class);

    // ==========================================
    // Routes spécialisées par module
    // ==========================================

    // Commandes
    Route::prefix('commandes/{commande}')->group(function () {
        Route::patch('/status', [CommandeController::class, 'updateStatus']);
        Route::post('/cancel', [CommandeController::class, 'cancel']);
        Route::get('/tracking', [CommandeController::class, 'tracking']);
    });

    // Produits
    Route::prefix('produits/{produit}')->group(function () {
        Route::post('/toggle-favorite', [ProduitController::class, 'toggleFavorite']);
        Route::get('/reviews', [ProduitController::class, 'reviews']);
        Route::post('/reviews', [ProduitController::class, 'addReview']);
    });

    // Promotions
    Route::prefix('promotions')->group(function () {
        Route::get('/active', [PromotionController::class, 'active']);
        Route::post('/{promotion}/apply', [PromotionController::class, 'apply']);
    });

    // Conversations
    Route::prefix('conversations/{conversation}')->group(function () {
        Route::post('/messages', [ConversationController::class, 'addMessage']);
        Route::patch('/close', [ConversationController::class, 'close']);
        Route::patch('/reopen', [ConversationController::class, 'reopen']);
    });

    // Livraisons
    Route::prefix('livraisons')->group(function () {
        Route::get('/tracking/{trackingNumber}', [LivraisonController::class, 'trackByNumber']);
        Route::patch('/{livraison}/status', [LivraisonController::class, 'updateStatus']);
    });

    // Factures
    Route::prefix('factures')->group(function () {
        Route::get('/{facture}/download', [FactureController::class, 'download']);
        Route::post('/{facture}/send-email', [FactureController::class, 'sendByEmail']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
        Route::get('/profile-completion', [DashboardController::class, 'profileCompletion']);
    });

    // ==========================================
    // Routes par rôles
    // ==========================================

    // Rôles admin
    Route::middleware('role:admin,super-admin')->group(function () {
        Route::prefix('admin/dashboard')->group(function () {
            Route::get('/overview', [DashboardController::class, 'adminOverview']);
            Route::get('/sales-stats', [DashboardController::class, 'salesStats']);
            Route::get('/user-stats', [DashboardController::class, 'userStats']);
            Route::get('/product-stats', [DashboardController::class, 'productStats']);
        });

        Route::prefix('admin/users')->group(function () {
            Route::get('/', [AuthController::class, 'listUsers']);
            Route::post('/', [AuthController::class, 'createUser']);
            Route::put('/{user}', [AuthController::class, 'updateUser']);
            Route::delete('/{user}', [AuthController::class, 'deleteUser']);
            Route::patch('/{user}/toggle-status', [AuthController::class, 'toggleUserStatus']);
        });
    });

    // Rôles employé
    Route::middleware('role:employe,admin,super-admin')->group(function () {
        Route::prefix('employee/dashboard')->group(function () {
            Route::get('/assigned-orders', [DashboardController::class, 'assignedOrders']);
            Route::get('/pending-tasks', [DashboardController::class, 'pendingTasks']);
        });
    });
});

// ==========================================
// Routes protégées Web (Sessions)
// ==========================================

Route::middleware(['web', 'auth'])->group(function () {
    // Ici vous pouvez ajouter des routes qui nécessitent une authentification par session
    // Par exemple des vues Blade, des formulaires web, etc.

    // Exemple :
    // Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    // Route::post('/web/profile', [AuthController::class, 'updateWebProfile']);
});

// ==========================================
// Route de fallback
// ==========================================

Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint non trouvé'
    ], 404);
});

use App\Http\Controllers\TestController;

// Ajoutez ces routes à votre fichier routes/api.php existant

// Routes de test
Route::get('/ping', [TestController::class, 'ping']);
Route::post('/csrf-test', [TestController::class, 'csrfTest']);

// Route de test sans CSRF (temporaire)
Route::post('/login-test', function () {
    return response()->json(['message' => 'Login test route accessible']);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
