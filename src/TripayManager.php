<?php

namespace Tripay\PPOB;

use Tripay\PPOB\Services\BalanceService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\ServerService;
use Tripay\PPOB\Services\TransactionService;

class TripayManager
{
    protected array $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * Get server service
     */
    public function server(): ServerService
    {
        return $this->services['server'];
    }

    /**
     * Get balance service
     */
    public function balance(): BalanceService
    {
        return $this->services['balance'];
    }

    /**
     * Get prepaid service
     */
    public function prepaid(): PrepaidService
    {
        return $this->services['prepaid'];
    }

    /**
     * Get postpaid service
     */
    public function postpaid(): PostpaidService
    {
        return $this->services['postpaid'];
    }

    /**
     * Get transaction service
     */
    public function transaction(): TransactionService
    {
        return $this->services['transaction'];
    }

    /**
     * Test connection to Tripay API
     */
    public function testConnection(): bool
    {
        return $this->server()->testConnection();
    }

    /**
     * Get current balance
     */
    public function getBalance(): float
    {
        return $this->balance()->getBalanceAmount();
    }

    /**
     * Quick method to purchase prepaid product
     */
    public function purchasePrepaid(string $productId, string $phoneNumber, string $apiTrxId, string $pin)
    {
        return $this->prepaid()->purchase($productId, $phoneNumber, $apiTrxId, $pin);
    }

    /**
     * Quick method to check bill
     */
    public function checkBill(string $productId, string $phoneNumber, string $customerNumber, string $pin, ?string $apiTrxId = null)
    {
        return $this->postpaid()->checkBillByParams($productId, $phoneNumber, $customerNumber, $pin, $apiTrxId);
    }

    /**
     * Quick method to pay bill
     */
    public function payBill(int $trxId, string $apiTrxId, string $pin)
    {
        return $this->postpaid()->payBillByParams($trxId, $apiTrxId, $pin);
    }
}
