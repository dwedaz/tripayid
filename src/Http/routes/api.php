<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tripay API Routes  
|--------------------------------------------------------------------------
|
| These routes handle incoming webhooks from Tripay when transaction
| status changes occur. These routes are automatically registered when
| the callback_url is configured in the tripay config file.
|
*/

// Webhook endpoint for receiving transaction status updates from Tripay
Route::post('/callback', function () {
    // TODO: Implement webhook handler
    // This should validate the signature and process the callback data
    return response()->json([
        'success' => true,
        'message' => 'Webhook received successfully'
    ]);
})->middleware('tripay.signature');

// Health check endpoint for Tripay
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'Tripay PPOB'
    ]);
});