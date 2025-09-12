<?php

namespace Tripay\PPOB\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tripay\PPOB\Facades\Tripay;
use Tripay\PPOB\Models\Category;
use Tripay\PPOB\Models\Operator;
use Tripay\PPOB\Models\Product;

class DashboardController extends Controller
{
    /**
     * Get current balance from Tripay API
     */
    public function balance(): JsonResponse
    {
        try {
            $balance = Tripay::getBalance();
            
            return response()->json([
                'success' => true,
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch balance: ' . $e->getMessage(),
                'balance' => 0
            ], 500);
        }
    }

    /**
     * Sync categories from Tripay API
     */
    public function syncCategories(): JsonResponse
    {
        try {
            $response = Tripay::prepaid()->getCategories();
            
            if (!$response->success) {
                throw new \Exception($response->message ?? 'Failed to fetch categories');
            }

            $syncedCount = 0;
            foreach ($response->data as $categoryData) {
                // Generate category_id from category_name if not provided by API
                $categoryId = $categoryData->category_id ?? $categoryData->id;
                if (empty($categoryId)) {
                    // Create ID from category name (slugified)
                    $categoryId = strtolower(str_replace([' ', '-', '_'], '', preg_replace('/[^A-Za-z0-9\s\-_]/', '', $categoryData->category_name ?? $categoryData->name ?? 'unknown')));
                }
                
                Category::updateOrCreate(
                    ['category_id' => $categoryId],
                    [
                        'category_name' => $categoryData->category_name ?? $categoryData->name,
                        'description' => $categoryData->description ?? null,
                        'status' => $categoryData->status ?? $categoryData->is_active ?? true,
                        'type' => $categoryData->type ?? 'prepaid',
                        'sort_order' => $categoryData->sort_order ?? 0,
                        'synced_at' => now(),
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} categories"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync operators from Tripay API
     */
    public function syncOperators(): JsonResponse
    {
        try {
            $response = Tripay::prepaid()->getOperators();
            
            if (!$response->success) {
                throw new \Exception($response->message ?? 'Failed to fetch operators');
            }

            $syncedCount = 0;
            foreach ($response->data as $operatorData) {
                // In Tripay API, operators use 'category_id' as operator_id and 'category_name' as operator_name
                $operatorId = $operatorData->category_id ?? $operatorData->operator_id ?? $operatorData->code ?? $operatorData->id;
                $operatorName = $operatorData->category_name ?? $operatorData->operator_name ?? $operatorData->name;
                $operatorCode = $operatorData->category_id ?? $operatorData->operator_code ?? $operatorData->code ?? null;
                
                Operator::updateOrCreate(
                    ['operator_id' => $operatorId],
                    [
                        'operator_name' => $operatorName,
                        'operator_code' => $operatorCode,
                        'description' => $operatorData->description ?? null,
                        'status' => $operatorData->status ?? $operatorData->is_active ?? true,
                        'type' => $operatorData->type ?? 'prepaid',
                        'logo_url' => $operatorData->logo_url ?? $operatorData->logo ?? null,
                        'sort_order' => $operatorData->sort_order ?? 0,
                        'synced_at' => now(),
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} operators"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync operators: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync products from Tripay API
     */
    public function syncProducts(): JsonResponse
    {
        try {
            $response = Tripay::prepaid()->getProducts();
            
            if (!$response->success) {
                throw new \Exception($response->message ?? 'Failed to fetch products');
            }

            $syncedCount = 0;
            foreach ($response->data as $productData) {
                Product::updateOrCreate(
                    ['product_id' => $productData->product_id ?? $productData->id],
                    [
                        'product_name' => $productData->product_name ?? $productData->name,
                        'category_id' => $productData->category_id,
                        'operator_id' => $productData->operator_id,
                        'product_price' => $productData->product_price ?? $productData->price ?? 0,
                        'selling_price' => $productData->selling_price ?? $productData->product_price ?? $productData->price ?? 0,
                        'profit_margin' => $productData->profit_margin ?? 0,
                        'description' => $productData->description ?? $productData->product_description ?? null,
                        'status' => $productData->status ?? $productData->is_active ?? true,
                        'type' => $productData->type ?? 'prepaid',
                        'denomination' => $productData->denomination ?? null,
                        'additional_info' => isset($productData->additional_info) ? json_encode($productData->additional_info) : null,
                        'cut_off_start' => $productData->cut_off_start ?? null,
                        'cut_off_end' => $productData->cut_off_end ?? null,
                        'sort_order' => $productData->sort_order ?? 0,
                        'is_featured' => $productData->is_featured ?? false,
                        'synced_at' => now(),
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} products"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all data from Tripay API
     */
    public function syncAll(): JsonResponse
    {
        try {
            $results = [];
            
            // Sync categories
            $categoriesResult = $this->syncCategories();
            $results['categories'] = json_decode($categoriesResult->getContent(), true);
            
            // Sync operators
            $operatorsResult = $this->syncOperators();
            $results['operators'] = json_decode($operatorsResult->getContent(), true);
            
            // Sync products
            $productsResult = $this->syncProducts();
            $results['products'] = json_decode($productsResult->getContent(), true);
            
            $totalSuccess = collect($results)->where('success', true)->count();
            
            return response()->json([
                'success' => $totalSuccess === 3,
                'message' => $totalSuccess === 3 
                    ? 'Successfully synced all data'
                    : "Synced {$totalSuccess}/3 data types successfully",
                'details' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync all data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            cache()->flush();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $isConnected = Tripay::testConnection();
            $balance = null;
            
            try {
                $balance = Tripay::getBalance();
            } catch (\Exception $e) {
                // Balance check failed, but connection might still be ok
            }
            
            return response()->json([
                'healthy' => $isConnected,
                'message' => $isConnected 
                    ? 'Tripay API connection is healthy' 
                    : 'Tripay API connection failed',
                'details' => [
                    'connection' => $isConnected,
                    'balance_check' => $balance !== null,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'healthy' => false,
                'message' => 'Health check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}