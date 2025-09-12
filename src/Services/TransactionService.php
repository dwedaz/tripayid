<?php

namespace Tripay\PPOB\Services;

use Tripay\PPOB\DTO\Response\TransactionHistoryResponse;
use Tripay\PPOB\DTO\Response\TransactionDetailResponse;

class TransactionService extends BaseService
{
    /**
     * Get all transactions history
     */
    public function getHistory(): TransactionHistoryResponse
    {
        $response = $this->client->get($this->getEndpoint('history'));
        return TransactionHistoryResponse::from($response);
    }

    /**
     * Get transaction history by date range
     */
    public function getHistoryByDate(string $startDate, string $endDate): TransactionHistoryResponse
    {
        $params = $this->buildQueryParams([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response = $this->client->get($this->getEndpoint('history_by_date'), $params);
        return TransactionHistoryResponse::from($response);
    }

    /**
     * Get transaction detail by API transaction ID
     */
    public function getDetail(string $apiTrxId): TransactionDetailResponse
    {
        $params = ['api_trxid' => $apiTrxId];
        $response = $this->client->post($this->getEndpoint('detail'), $params);
        return TransactionDetailResponse::from($response);
    }

    /**
     * Get transaction detail by Tripay transaction ID
     */
    public function getDetailByTrxId(int $trxId): TransactionDetailResponse
    {
        $params = ['trxid' => $trxId];
        $response = $this->client->post($this->getEndpoint('detail'), $params);
        return TransactionDetailResponse::from($response);
    }

    /**
     * Get cached transaction history
     */
    public function getCachedHistory(int $ttl = 300): TransactionHistoryResponse
    {
        $response = $this->getCachedData('transaction_history', $this->getEndpoint('history'), [], $ttl);
        return TransactionHistoryResponse::from($response);
    }

    /**
     * Get recent transactions (today)
     */
    public function getTodayTransactions(): TransactionHistoryResponse
    {
        $today = date('Y-m-d');
        return $this->getHistoryByDate($today, $today);
    }

    /**
     * Get transactions for current month
     */
    public function getThisMonthTransactions(): TransactionHistoryResponse
    {
        $firstDay = date('Y-m-01');
        $lastDay = date('Y-m-t');
        return $this->getHistoryByDate($firstDay, $lastDay);
    }

    /**
     * Get pending transactions
     */
    public function getPendingTransactions(): TransactionHistoryResponse
    {
        $allTransactions = $this->getHistory();
        
        // Filter for pending status
        $pendingTransactions = array_filter($allTransactions->data, function ($transaction) {
            return strtolower($transaction->status) === 'pending';
        });

        return TransactionHistoryResponse::from([
            'success' => $allTransactions->success,
            'message' => $allTransactions->message,
            'data' => array_values($pendingTransactions)
        ]);
    }

    /**
     * Get successful transactions
     */
    public function getSuccessfulTransactions(): TransactionHistoryResponse
    {
        $allTransactions = $this->getHistory();
        
        // Filter for success status
        $successTransactions = array_filter($allTransactions->data, function ($transaction) {
            return strtolower($transaction->status) === 'success';
        });

        return TransactionHistoryResponse::from([
            'success' => $allTransactions->success,
            'message' => $allTransactions->message,
            'data' => array_values($successTransactions)
        ]);
    }

    /**
     * Get failed transactions
     */
    public function getFailedTransactions(): TransactionHistoryResponse
    {
        $allTransactions = $this->getHistory();
        
        // Filter for failed status
        $failedTransactions = array_filter($allTransactions->data, function ($transaction) {
            return in_array(strtolower($transaction->status), ['failed', 'error', 'gagal']);
        });

        return TransactionHistoryResponse::from([
            'success' => $allTransactions->success,
            'message' => $allTransactions->message,
            'data' => array_values($failedTransactions)
        ]);
    }

    /**
     * Search transactions by product name or API transaction ID
     */
    public function searchTransactions(string $search): TransactionHistoryResponse
    {
        $allTransactions = $this->getHistory();
        
        // Filter transactions by search term
        $filteredTransactions = array_filter($allTransactions->data, function ($transaction) use ($search) {
            return stripos($transaction->product_name, $search) !== false ||
                   stripos($transaction->api_trxid, $search) !== false;
        });

        return TransactionHistoryResponse::from([
            'success' => $allTransactions->success,
            'message' => $allTransactions->message,
            'data' => array_values($filteredTransactions)
        ]);
    }

    protected function getEndpoint(string $action): string
    {
        return match ($action) {
            'history' => '/histori/transaksi/all',
            'history_by_date' => '/histori/transaksi/bydate',
            'detail' => '/histori/transaksi/detail',
            default => throw new \InvalidArgumentException("Unknown action: $action")
        };
    }
}