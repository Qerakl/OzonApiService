<?php

use App\Http\Controllers\MarketplaceApiKeyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        // Список API-ключей: /marketplace/api-keys
        Route::get('/api-keys', [MarketplaceApiKeyController::class, 'index'])
            ->name('api-keys.index');
        Route::post('/api-keys', [MarketplaceApiKeyController::class, 'store'])->name('api-keys.store');
    });

});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
