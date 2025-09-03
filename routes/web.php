<?php

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
