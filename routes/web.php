<?php

use App\Core\Facades\Route;
use App\Http\Controllers\BankApplicationController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\Development\HealthController;
use App\Http\Middlewares\GuestMiddleware;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EncodeController;

// ! DEVELOPMENT Routes
Route::get('/health', HealthController::class);

Route::middleware(AuthMiddleware::class)->group(function () {

    /** 
     * -----------------------------
     * Views
     * -----------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'show']);
    Route::get('/bank-applications', [BankApplicationController::class, 'show']);
    Route::post('/bank-applications/pre-export', [BankApplicationController::class, 'preExport']);
    Route::post('/bank-applications/export', [BankApplicationController::class, 'export']);

    Route::controller(EncodeController::class)->group(function() {
        Route::get('/encode', 'show');
        Route::post('/encode', 'store');
        Route::post('/encode-check', 'check');
    });
    
    /** 
     * -----------------------------
     * Dashboard Chart API's 
     * -----------------------------
    */

    Route::post('/dashboard/chart/client-type-today', [ChartsController::class, 'clientTypeToday']);
    Route::post('/dashboard/chart/client-type-series', [ChartsController::class, 'clientTypeSeries']);
    
    Route::post('/dashboard/chart/bank-apps-today', [ChartsController::class, 'bankAppsToday']);
    Route::post('/dashboard/chart/bank-apps-series', [ChartsController::class, 'bankAppsSeries']);
    
    Route::post('/dashboard/chart/agents-leaderboards', [ChartsController::class, 'agentsLeaderboards']);

    /** 
     * -----------------------------
     * Encode API's 
     * -----------------------------
    */

    Route::post('/logout', [LoginController::class, 'logout']);
});

Route::middleware(GuestMiddleware::class)->group(function() {
    Route::get('/login', [LoginController::class, 'show']);
    Route::post('/login', [LoginController::class, 'login']);
});