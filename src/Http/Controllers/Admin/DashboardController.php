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
                Category::updateOrCreate(
                    ['category_id' => $categoryData->category_id],
                    [
                        'category_name' => $categoryData->category_name,
                        'is_active' => $categoryData->is_active ?? true,
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
                Operator::updateOrCreate(
                    ['category_id' => $operatorData->category_id],
                    [
                        'category_name' => $operatorData->category_name,
                        'is_active' => $operatorData->is_active ?? true,
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
                    ['product_id' => $productData->product_id],
                    [
                        'product_name' => $productData->product_name,
                        'category_id' => $productData->category_id,
                        'operator_id' => $productData->operator_id ?? null,
                        'product_price' => $productData->product_price,
                        'is_active' => $productData->is_active ?? true,
                        'product_description' => $productData->product_description ?? null,
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