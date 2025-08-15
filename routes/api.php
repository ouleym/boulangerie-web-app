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

// Routes publiques (consultation produits/categories)
Route::get('/categories', [CategorieController::class, 'index']);
Route::get('/categories/{categorie}', [CategorieController::class, 'show']);
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{produit}', [ProduitController::class, 'show']);

// Routes protégées par authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard & Statistiques
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Notifications
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'update']);
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead']);

    // Commandes (tous les utilisateurs authentifiés)
    Route::apiResource('commandes', CommandeController::class);
    Route::patch('/commandes/{commande}/statut', [CommandeController::class, 'updateStatut']);

    // Chat / Conversations
    Route::apiResource('conversations', ConversationController::class);
    Route::apiResource('conversations.messages', MessageController::class)->shallow();

    // Factures
    Route::get('/factures', [FactureController::class, 'index']);
    Route::get('/factures/{facture}', [FactureController::class, 'show']);
    Route::get('/factures/{facture}/download', [FactureController::class, 'download']);

    // Routes pour les rôles Admin et Employé
    Route::middleware('role:admin|employe')->group(function () {

        // Gestion des catégories
        Route::apiResource('categories', CategorieController::class)->except(['index', 'show']);

        // Gestion des produits
        Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
        Route::patch('/produits/{produit}/stock', [ProduitController::class, 'updateStock']);

        // Gestion des promotions
        Route::apiResource('promotions', PromotionController::class);

        // Livraisons
        Route::apiResource('livraisons', LivraisonController::class);
        Route::patch('/livraisons/{livraison}/statut', [LivraisonController::class, 'updateStatut']);

        // Factures (génération)
        Route::post('/factures', [FactureController::class, 'store']);
        Route::post('/factures/{facture}/send', [FactureController::class, 'sendByEmail']);
    });

    // Routes spécifiques aux Administrateurs
    Route::middleware('role:admin')->group(function () {

        // Gestion des utilisateurs
        Route::get('/users', [AuthController::class, 'indexUsers']);
        Route::patch('/users/{user}/toggle-status', [AuthController::class, 'toggleStatus']);

        // Statistiques avancées
        Route::get('/stats/ventes', [DashboardController::class, 'ventesStats']);
        Route::get('/stats/produits', [DashboardController::class, 'produitsStats']);
        Route::get('/stats/clients', [DashboardController::class, 'clientsStats']);
    });
});

// Route de fallback pour API
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint non trouvé'
    ], 404);
});
