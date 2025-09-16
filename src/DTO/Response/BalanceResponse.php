<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class BalanceResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?float $saldo;
    public readonly ?string $currency;

    /**
     * Parse balance amount from message
     * Expected message format: "Saldo anda Rp. 398.806"
     */
    public function getBalanceAmount(): float
    {
        // If saldo field exists and is initialized, use it directly
        if (isset($this->saldo) && $this->saldo !== null) {
            return $this->saldo;
        }

        // Parse balance from message
        if (preg_match('/Rp\.?\s*([\d.,]+)/', $this->message, $matches)) {
            $amount = $matches[1];
            // Remove dots (thousand separators) and replace comma with dot (decimal separator)
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
            return (float) $amount;
        }

        return 0.0;
    }
}
