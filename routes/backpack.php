<?php

use Illuminate\Support\Facades\Route;
use Tripay\PPOB\Http\Controllers\Admin\CategoryCrudController;
use Tripay\PPOB\Http\Controllers\Admin\DashboardController;
use Tripay\PPOB\Http\Controllers\Admin\OperatorCrudController;
use Tripay\PPOB\Http\Controllers\Admin\ProductCrudController;
use Tripay\PPOB\Http\Controllers\Admin\TransactionCrudController;

/*
|--------------------------------------------------------------------------
| Tripay PPOB Admin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the TripayServiceProvider within a group 
| which contains the "web" and "admin" middleware groups.
|
*/

// Admin dashboard and overview routes
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    
    // Tripay Dashboard
    Route::get('tripay', function () {
        return view('tripay::admin.dashboard');
    })->name('tripay.dashboard');

    // Categories CRUD routes
    Route::crud('tripay/categories', CategoryCrudController::class);
    
    // Operators CRUD routes
    Route::crud('tripay/operators', OperatorCrudController::class);
    
    // Products CRUD routes  
    Route::crud('tripay/products', ProductCrudController::class);
    
    // Transactions CRUD routes (read-only)
    Route::crud('tripay/transactions', TransactionCrudController::class);

    // Dashboard and management routes
    Route::group(['prefix' => 'tripay'], function () {
        
        // Balance check
        Route::get('balance', [DashboardController::class, 'balance'])->name('tripay.balance');
        
        // Sync routes
        Route::post('sync/categories', [DashboardController::class, 'syncCategories'])->name('tripay.sync.categories');
        Route::post('sync/operators', [DashboardController::class, 'syncOperators'])->name('tripay.sync.operators');  
        Route::post('sync/products', [DashboardController::class, 'syncProducts'])->name('tripay.sync.products');
        Route::post('sync/all', [DashboardController::class, 'syncAll'])->name('tripay.sync.all');
        
        // Cache management
        Route::post('cache/clear', [DashboardController::class, 'clearCache'])->name('tripay.cache.clear');
        
        // Health check
        Route::get('health', [DashboardController::class, 'healthCheck'])->name('tripay.health');
        
        // Test route
        Route::get('test', function() {
            return response()->json(['message' => 'Tripay admin routes working!', 'time' => now()]);
        })->name('tripay.test');
    });
});