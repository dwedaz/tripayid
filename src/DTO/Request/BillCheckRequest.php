<?php

namespace Tripay\PPOB\DTO\Request;

use Tripay\PPOB\DTO\DataTransferObject;

class BillCheckRequest extends DataTransferObject
{
    public function __construct(
        public readonly string $product,
        public readonly string $phone,
        public readonly string $no_pelanggan,
        public readonly string $pin,
        public readonly ?string $api_trxid = null,
    ) {
        parent::__construct([
            'product' => $product,
            'phone' => $phone,
            'no_pelanggan' => $no_pelanggan,
            'pin' => $pin,
            'api_trxid' => $api_trxid,
        ]);
    }

    public static function create(
        string $product,
        string $phone,
        string $customerNumber,
        string $pin,
        ?string $apiTrxId = null
    ): static {
        return new static($product, $phone, $customerNumber, $pin, $apiTrxId);
    }
}