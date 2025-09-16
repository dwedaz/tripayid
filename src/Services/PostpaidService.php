<?php

namespace Tripay\PPOB\Services;

use Tripay\PPOB\DTO\Request\BillCheckRequest;
use Tripay\PPOB\DTO\Request\BillPaymentRequest;
use Tripay\PPOB\DTO\Response\BillCheckResponse;
use Tripay\PPOB\DTO\Response\CategoryResponse;
use Tripay\PPOB\DTO\Response\OperatorResponse;
use Tripay\PPOB\DTO\Response\ProductDetailResponse;
use Tripay\PPOB\DTO\Response\ProductResponse;
use Tripay\PPOB\DTO\Response\TransactionResponse;

class PostpaidService extends BaseService
{
    /**
     * Get postpaid categories
     */
    public function getCategories(): CategoryResponse
    {
        $response = $this->getCachedData('postpaid_categories', $this->getEndpoint('categories'));

        return CategoryResponse::from($response);
    }

    /**
     * Get postpaid operators
     */
    public function getOperators(): OperatorResponse
    {
        $response = $this->getCachedData('postpaid_operators', $this->getEndpoint('operators'));

        return OperatorResponse::from($response);
    }

    /**
     * Get postpaid products
     */
    public function getProducts(?string $category = null, ?string $operator = null): ProductResponse
    {
        $params = $this->buildQueryParams([
            'category' => $category,
            'operator' => $operator,
        ]);

        $cacheKey = 'postpaid_products_' . md5(serialize($params));
        $response = $this->getCachedData($cacheKey, $this->getEndpoint('products'), $params);

        return ProductResponse::from($response);
    }

    /**
     * Get product details
     */
    public function getProductDetail(string $productId): ProductDetailResponse
    {
        $params = ['product' => $productId];
        $cacheKey = 'postpaid_product_detail_' . $productId;

        $response = $this->getCachedData($cacheKey, $this->getEndpoint('product_detail'), $params);

        return ProductDetailResponse::from($response);
    }

    /**
     * Check bill/tagihan
     */
    public function checkBill(BillCheckRequest $request): BillCheckResponse
    {
        $response = $this->client->post($this->getEndpoint('check_bill'), $request->toArray());

        return BillCheckResponse::from($response);
    }

    /**
     * Check bill with parameters
     */
    public function checkBillByParams(
        string $productId,
        string $phoneNumber,
        string $customerNumber,
        string $pin,
        ?string $apiTrxId = null
    ): BillCheckResponse {
        $request = BillCheckRequest::create($productId, $phoneNumber, $customerNumber, $pin, $apiTrxId);

        return $this->checkBill($request);
    }

    /**
     * Pay bill
     */
    public function payBill(BillPaymentRequest $request): TransactionResponse
    {
        $response = $this->client->post($this->getEndpoint('pay_bill'), $request->toArray());

        return TransactionResponse::from($response);
    }

    /**
     * Pay bill with parameters
     */
    public function payBillByParams(
        int $trxId,
        string $apiTrxId,
        string $pin
    ): TransactionResponse {
        $request = BillPaymentRequest::create($trxId, $apiTrxId, $pin);

        return $this->payBill($request);
    }

    /**
     * Check and pay bill in one flow
     */
    public function checkAndPayBill(
        string $productId,
        string $phoneNumber,
        string $customerNumber,
        string $apiTrxId,
        string $pin,
        bool $autoPay = false
    ): BillCheckResponse|TransactionResponse {
        // First check the bill
        $billCheck = $this->checkBillByParams($productId, $phoneNumber, $customerNumber, $pin, $apiTrxId);

        if (! $billCheck->success || ! $autoPay) {
            return $billCheck;
        }

        // If successful and autoPay is enabled, proceed with payment
        if ($billCheck->trxid) {
            return $this->payBillByParams($billCheck->trxid, $apiTrxId, $pin);
        }

        return $billCheck;
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
            'data' => array_values($filteredProducts),
        ]);
    }

    protected function getEndpoint(string $action): string
    {
        return match ($action) {
            'categories' => '/pembayaran/category',
            'operators' => '/pembayaran/operator',
            'products' => '/pembayaran/produk',
            'product_detail' => '/pembayaran/produk/cek',
            'check_bill' => '/pembayaran/cek-tagihan',
            'pay_bill' => '/transaksi/pembayaran',
            default => throw new \InvalidArgumentException("Unknown action: $action")
        };
    }
}
