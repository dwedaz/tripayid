<?php

namespace Tripay\PPOB\Services;

use Tripay\PPOB\DTO\Response\CategoryResponse;
use Tripay\PPOB\DTO\Response\ProductResponse;
use Tripay\PPOB\DTO\Response\ProductDetailResponse;
use Tripay\PPOB\DTO\Response\TransactionResponse;
use Tripay\PPOB\DTO\Request\PrepaidTransactionRequest;

class PrepaidService extends BaseService
{
    /**
     * Get prepaid categories
     */
    public function getCategories(): CategoryResponse
    {
        $response = $this->getCachedData('prepaid_categories', $this->getEndpoint('categories'));
        return CategoryResponse::from($response);
    }

    /**
     * Get prepaid operators
     */
    public function getOperators(): CategoryResponse
    {
        $response = $this->getCachedData('prepaid_operators', $this->getEndpoint('operators'));
        return CategoryResponse::from($response);
    }

    /**
     * Get prepaid products
     */
    public function getProducts(?string $category = null, ?string $operator = null): ProductResponse
    {
        $params = $this->buildQueryParams([
            'category' => $category,
            'operator' => $operator,
        ]);

        $cacheKey = 'prepaid_products_' . md5(serialize($params));
        $response = $this->getCachedData($cacheKey, $this->getEndpoint('products'), $params);
        
        return ProductResponse::from($response);
    }

    /**
     * Get product details
     */
    public function getProductDetail(string $productId): ProductDetailResponse
    {
        $params = ['product' => $productId];
        $cacheKey = 'prepaid_product_detail_' . $productId;
        
        $response = $this->getCachedData($cacheKey, $this->getEndpoint('product_detail'), $params);
        return ProductDetailResponse::from($response);
    }

    /**
     * Create prepaid transaction
     */
    public function createTransaction(PrepaidTransactionRequest $request): TransactionResponse
    {
        $response = $this->client->post($this->getEndpoint('transaction'), $request->toArray());
        return TransactionResponse::from($response);
    }

    /**
     * Purchase prepaid product
     */
    public function purchase(
        string $productId,
        string $phoneNumber,
        string $apiTrxId,
        string $pin
    ): TransactionResponse {
        $request = PrepaidTransactionRequest::create($productId, $phoneNumber, $apiTrxId, $pin);
        return $this->createTransaction($request);
    }

    /**
     * Get products by operator
     */
    public function getProductsByOperator(string $operator): ProductResponse
    {
        return $this->getProducts(null, $operator);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(string $category): ProductResponse
    {
        return $this->getProducts($category);
    }

    /**
     * Search products by name
     */
    public function searchProducts(string $search): ProductResponse
    {
        $allProducts = $this->getProducts();
        
        // Filter products by search term
        $filteredProducts = array_filter($allProducts->data, function ($product) use ($search) {
            return stripos($product->product_name, $search) !== false;
        });

        // Return new ProductResponse with filtered data
        return ProductResponse::from([
            'success' => $allProducts->success,
            'message' => $allProducts->message,
            'data' => array_values($filteredProducts)
        ]);
    }

    protected function getEndpoint(string $action): string
    {
        return match ($action) {
            'categories' => '/pembelian/category',
            'operators' => '/pembelian/operator',
            'products' => '/pembelian/produk',
            'product_detail' => '/pembelian/produk/cek',
            'transaction' => '/transaksi/pembelian',
            default => throw new \InvalidArgumentException("Unknown action: $action")
        };
    }
}