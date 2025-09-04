<?php

use App\Http\Controllers\Admin\LivraisonController;
use App\Http\Controllers\FactureController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PaiementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Routes pour les callbacks CinetPay
Route::get('/payment/success', [PaiementController::class, 'success'])->name('paiement.success');
Route::post('/payment/notify', [PaiementController::class, 'notify'])->name('paiement.notify');



Route::middleware('auth')->group(function () {

    // Routes pour les clients
    Route::middleware(['role:Client'])->group(function () {
        Route::get('/client/facture/{commande}/download', [FactureController::class, 'telecharger'])
            ->name('client.facture.download')
            ->middleware('permission:download invoices');
        Route::get('/client/facture/{commande}', [FactureController::class, 'afficher'])
            ->name('client.facture.show')
            ->middleware('permission:view orders');
    });

    // Routes pour les admins et employés
    Route::middleware(['role:Admin|Employe'])->group(function () {
        Route::get('/admin/factures', [FactureController::class, 'index'])
            ->name('admin.factures.index')
            ->middleware('permission:view invoices');
        Route::post('/admin/facture/{commande}/renvoyer', [FactureController::class, 'renvoyer'])
            ->name('admin.facture.renvoyer')
            ->middleware('permission:send invoices');
        Route::get('/admin/facture/{commande}/download', [FactureController::class, 'telecharger'])
            ->name('admin.facture.download')
            ->middleware('permission:download invoices');
        Route::get('/admin/facture/{commande}', [FactureController::class, 'afficher'])
            ->name('admin.facture.show')
            ->middleware('permission:view invoices');
        Route::post('/admin/factures/download-multiples', [FactureController::class, 'telechargerMultiples'])
            ->name('admin.factures.download-multiples')
            ->middleware('permission:download invoices');
    });
});

// Routes pour la gestion des livraisons
Route::middleware(['auth', 'role:Admin|Employe'])->group(function () {
    Route::patch('/admin/livraisons/{livraison}/status', [LivraisonController::class, 'updateStatus'])
        ->name('admin.livraisons.update-status')
        ->middleware('permission:manage deliveries');
    Route::post('/admin/livraisons/update-multiple-status', [LivraisonController::class, 'updateMultipleStatus'])
        ->name('admin.livraisons.update-multiple-status')
        ->middleware('permission:manage deliveries');
    Route::get('/admin/livraisons', [LivraisonController::class, 'index'])
        ->name('admin.livraisons.index')
        ->middleware('permission:view deliveries');
    Route::get('/admin/livraisons/{livraison}', [LivraisonController::class, 'show'])
        ->name('admin.livraisons.show')
        ->middleware('permission:view deliveries');
    Route::get('/admin/livraisons/dashboard', [LivraisonController::class, 'dashboard'])
        ->name('admin.livraisons.dashboard')
        ->middleware('permission:view deliveries');
});
