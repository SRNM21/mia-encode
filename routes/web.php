<?php

use App\Core\Facades\Route;
use App\Http\Middlewares\GuestMiddleware;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Middlewares\AdminMiddleware;
use App\Http\Middlewares\EncoderMiddleware;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncodeController;
use App\Http\Controllers\BankApplicationController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\Development\HealthController;
use App\Http\Controllers\LeaderboardsController;
use App\Http\Controllers\SettingsController;

// ! DEVELOPMENT Routes
Route::get('/up', HealthController::class);
Route::get('/', [LoginController::class, 'redirectUser']);

Route::middleware(GuestMiddleware::class)->group(function () {
    Route::get('/login', [LoginController::class, 'show']);
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(AuthMiddleware::class)->group(function () {

    // Shared
    Route::get('/bank-applications', [BankApplicationController::class, 'show']);
    Route::post('/bank-applications/pre-export', [BankApplicationController::class, 'preExport']);
    Route::post('/bank-applications/export', [BankApplicationController::class, 'export']);
    
    Route::post('/logout', [LoginController::class, 'logout']);

    // Admin
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'show']);

        Route::controller(ChartsController::class)->group(function() {
            Route::post('/dashboard/chart/client-type-today', 'clientTypeToday');
            Route::post('/dashboard/chart/client-type-series', 'clientTypeSeries');
            
            Route::post('/dashboard/chart/bank-apps-today', 'bankAppsToday');
            Route::post('/dashboard/chart/bank-apps-series', 'bankAppsSeries');
            
            Route::post('/dashboard/chart/agents-leaderboards', 'agentsLeaderboards');
        });
        
        Route::get('/leaderboards', [LeaderboardsController::class, 'show']);

        Route::get('/banks', [BankController::class, 'show']);
        Route::post('/banks', [BankController::class, 'store']);
        Route::patch('/banks', [BankController::class, 'update']);
        
        // TODO: ADD NOTIFICATION / REQUEST
        // TODO: ADD ACCOUNT MANAGER

        // TODO: SETTINGS [THEME, ACCOUNT]
        // Route::get('/settings', [SettingsController::class, 'show']);
    });

    // Encoder
    Route::middleware(EncoderMiddleware::class)->group(function () {
        Route::controller(EncodeController::class)->group(function() {
            Route::get('/encode', 'show');
            Route::post('/encode', 'store');
            Route::post('/encode-check', 'check');
        });

        Route::patch('/bank-applications', [BankApplicationController::class, 'update']);

        // TODO: REQUEST CLIENT EDIT
        // TODO: REQUEST EDIT ACCOUNT
    });
});