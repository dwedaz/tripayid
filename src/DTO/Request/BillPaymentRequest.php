<?php

namespace Tripay\PPOB\DTO\Request;

use Tripay\PPOB\DTO\DataTransferObject;

class BillPaymentRequest extends DataTransferObject
{
    public function __construct(
        public readonly int $trxid,
        public readonly string $api_trxid,
        public readonly string $pin,
    ) {
        parent::__construct([
            'trxid' => $trxid,
            'api_trxid' => $api_trxid,
            'pin' => $pin,
        ]);
    }

    public static function create(
        int $trxId,
        string $apiTrxId,
        string $pin
    ): static {
        return new static($trxId, $apiTrxId, $pin);
    }
}
