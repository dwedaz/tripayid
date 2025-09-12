<?php

namespace Tripay\PPOB\DTO\Request;

use Tripay\PPOB\DTO\DataTransferObject;

class PrepaidTransactionRequest extends DataTransferObject
{
    public function __construct(
        public readonly string $product,
        public readonly string $phone,
        public readonly string $api_trxid,
        public readonly string $pin,
    ) {
        parent::__construct([
            'product' => $product,
            'phone' => $phone,
            'api_trxid' => $api_trxid,
            'pin' => $pin,
        ]);
    }

    public static function create(
        string $product,
        string $phone,
        string $apiTrxId,
        string $pin
    ): static {
        return new static($product, $phone, $apiTrxId, $pin);
    }
}