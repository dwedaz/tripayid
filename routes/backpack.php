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
    'namespace' => 'Tripay\PPOB\Http\Controllers\Admin',
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

    // Sync and management routes
    Route::group(['prefix' => 'tripay'], function () {
        
        // Sync routes
        Route::post('sync/categories', 'SyncController@syncCategories')->name('tripay.sync.categories');
        Route::post('sync/operators', 'SyncController@syncOperators')->name('tripay.sync.operators');  
        Route::post('sync/products', 'SyncController@syncProducts')->name('tripay.sync.products');
        Route::post('sync/all', 'SyncController@syncAll')->name('tripay.sync.all');
        
        // Balance check
        Route::get('balance', 'DashboardController@balance')->name('tripay.balance');
        
        // Transaction management
        Route::post('transactions/{transaction}/retry', 'TransactionController@retry')->name('tripay.transactions.retry');
        Route::post('transactions/{transaction}/cancel', 'TransactionController@cancel')->name('tripay.transactions.cancel');
        
        // Reports
        Route::get('reports', 'ReportController@index')->name('tripay.reports');
        Route::get('reports/export', 'ReportController@export')->name('tripay.reports.export');
        
        // Settings
        Route::get('settings', 'SettingsController@index')->name('tripay.settings');
        Route::post('settings', 'SettingsController@update')->name('tripay.settings.update');
        
        // Health check
        Route::get('health', 'HealthController@check')->name('tripay.health.check');
        
        // Cache management
        Route::post('cache/clear', 'CacheController@clear')->name('tripay.cache.clear');
        Route::post('cache/warm', 'CacheController@warm')->name('tripay.cache.warm');
    });
});