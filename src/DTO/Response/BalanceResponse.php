<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class BalanceResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?float $saldo;
    public readonly ?string $currency;
}