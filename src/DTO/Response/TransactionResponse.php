<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class TransactionResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?int $trxid;
    public readonly ?string $api_trxid;
}

class BillCheckData extends DataTransferObject
{
    public readonly string $product_id;
    public readonly string $product_name;
    public readonly string $customer_name;
    public readonly string $customer_phone;
    public readonly string $customer_no;
    public readonly float $amount;
    public readonly float $admin_fee;
    public readonly float $total_amount;
    public readonly string $period;
    public readonly string $due_date;
    public readonly ?array $additional_info;
}

class BillCheckResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?string $product_name;
    public readonly ?string $via;
    public readonly ?int $trxid;
    public readonly ?string $api_trxid;
    public readonly ?BillCheckData $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);

        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = BillCheckData::from($data['data']);
        }
    }
}

class TransactionHistoryData extends DataTransferObject
{
    public readonly int $trxid;
    public readonly string $api_trxid;
    public readonly string $product_id;
    public readonly string $product_name;
    public readonly string $target;
    public readonly float $price;
    public readonly float $selling_price;
    public readonly string $status;
    public readonly string $created_at;
    public readonly ?string $updated_at;
    public readonly ?string $sn; // serial number for prepaid
    public readonly ?string $note;
}

class TransactionHistoryResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var TransactionHistoryData[] */
    public readonly array $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);

        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = array_map(
                fn ($item) => TransactionHistoryData::from($item),
                $data['data']
            );
        }
    }
}

class TransactionDetailResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?TransactionHistoryData $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);

        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = TransactionHistoryData::from($data['data']);
        }
    }
}
