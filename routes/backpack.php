<?php

use Illuminate\Support\Facades\Route;
use Tripay\PPOB\Http\Controllers\Admin\CategoryCrudController;
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

    // Sync and management routes (these will be implemented later)
    Route::group(['prefix' => 'tripay'], function () {
        
        // Sync routes - these will need to be implemented
        // Route::post('sync/categories', [SyncController::class, 'syncCategories'])->name('tripay.sync.categories');
        // Route::post('sync/operators', [SyncController::class, 'syncOperators'])->name('tripay.sync.operators');  
        // Route::post('sync/products', [SyncController::class, 'syncProducts'])->name('tripay.sync.products');
        // Route::post('sync/all', [SyncController::class, 'syncAll'])->name('tripay.sync.all');
        
        // Balance check - will be implemented
        // Route::get('balance', [DashboardController::class, 'balance'])->name('tripay.balance');
        
        // For now, let's add a simple test route
        Route::get('test', function() {
            return response()->json(['message' => 'Tripay admin routes working!', 'time' => now()]);
        })->name('tripay.test');
    });
});