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
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\RequestEditController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Development\HealthController;

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
    
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword']);
    Route::patch('/settings/theme', [SettingsController::class, 'updateTheme']);
        
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
            
            Route::post('/dashboard/chart/weekly-bank-table', 'weeklyCalendar');
        });
        
        Route::get('/leaderboards', [LeaderboardController::class, 'show']);

        Route::get('/banks', [BankController::class, 'show']);
        Route::post('/banks', [BankController::class, 'store']);
        Route::patch('/banks', [BankController::class, 'update']);
        Route::post('/banks/table', [BankController::class, 'table']);
        
        Route::get('/requests', [RequestEditController::class, 'show']);
        Route::patch('/requests/read', [RequestEditController::class, 'read']);
        Route::patch('/requests/reject', [RequestEditController::class, 'reject']);
        Route::patch('/requests/approve', [RequestEditController::class, 'approve']);
    });

    // Encoder
    Route::middleware(EncoderMiddleware::class)->group(function () {
        Route::controller(EncodeController::class)->group(function() {
            Route::get('/encode', 'show');
            Route::post('/encode', 'store');
            Route::post('/encode-check', 'check');
        });

        Route::post('/bank-applications/table', [BankApplicationController::class, 'table']);
        Route::patch('/bank-applications', [BankApplicationController::class, 'update']);
        
        Route::post('/bank-applications/check-edit', [BankApplicationController::class, 'checkEdit']);
        Route::get('/bank-applications/edit', [BankApplicationController::class, 'edit']);

        Route::post('/request-edit', [RequestEditController::class, 'store']);
        Route::delete('/request-edit', [RequestEditController::class, 'destroy']);
    });
});