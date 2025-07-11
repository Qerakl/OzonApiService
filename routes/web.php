<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceApiKeyController;
use App\Http\Controllers\MarketplaceForecastController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/api-keys', [MarketplaceApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [MarketplaceApiKeyController::class, 'store'])->name('api-keys.store');
        Route::put('/api-keys/{apiKey}', [MarketplaceApiKeyController::class, 'update'])->name('api-keys.update');
        Route::delete('/api-keys/{apiKey}', [MarketplaceApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    });


    Route::get('/marketplace/forecasts', [MarketplaceForecastController::class, 'index'])->name('marketplace.forecasts.index');
    Route::post('/marketplace/forecasts/calculate', [MarketplaceForecastController::class, 'calculate'])->name('marketplace.forecasts.calculate');
    Route::post('/marketplace/forecasts/period', [MarketplaceForecastController::class, 'forecastByPeriod']);

    Route::get('/marketplace/forecasts/charts', [App\Http\Controllers\ForecastController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
