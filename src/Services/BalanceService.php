<?php

namespace Tripay\PPOB\Services;

use Tripay\PPOB\DTO\Response\BalanceResponse;

class BalanceService extends BaseService
{
    /**
     * Get account balance
     */
    public function getBalance(): BalanceResponse
    {
        $response = $this->client->get($this->getEndpoint('check'));
        return BalanceResponse::from($response);
    }

    /**
     * Get cached balance with shorter TTL
     */
    public function getCachedBalance(int $ttl = 300): BalanceResponse
    {
        $response = $this->getCachedData('balance', $this->getEndpoint('check'), [], $ttl);
        return BalanceResponse::from($response);
    }

    /**
     * Get balance amount as float
     */
    public function getBalanceAmount(): float
    {
        $balance = $this->getBalance();
        return $balance->saldo ?? 0.0;
    }

    /**
     * Check if balance is sufficient for amount
     */
    public function isSufficientBalance(float $amount): bool
    {
        $balance = $this->getBalanceAmount();
        return $balance >= $amount;
    }

    protected function getEndpoint(string $action): string
    {
        return match ($action) {
            'check' => '/ceksaldo',
            default => throw new \InvalidArgumentException("Unknown action: $action")
        };
    }
}