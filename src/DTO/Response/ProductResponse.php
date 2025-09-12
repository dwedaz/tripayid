<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class ProductData extends DataTransferObject
{
    public readonly string $product_id;
    public readonly string $product_name;
    public readonly ?string $category;
    public readonly ?string $operator;
    public readonly ?float $product_price;
    public readonly ?float $selling_price;
    public readonly ?string $description;
    public readonly ?bool $status;
    public readonly ?string $type; // prepaid or postpaid
    
    public function __construct(array $data = [])
    {
        $this->product_id = $data['product_id'] ?? '';
        $this->product_name = $data['product_name'] ?? '';
        $this->category = $data['category'] ?? null;
        $this->operator = $data['operator'] ?? null;
        $this->product_price = isset($data['product_price']) ? (float)$data['product_price'] : (isset($data['price']) ? (float)$data['price'] : null);
        $this->selling_price = isset($data['selling_price']) ? (float)$data['selling_price'] : null;
        $this->description = $data['description'] ?? null;
        $this->status = isset($data['status']) ? (bool)$data['status'] : null;
        $this->type = $data['type'] ?? null;
    }
}

class ProductDetailData extends ProductData
{
    public readonly ?array $additional_info;
    public readonly ?string $cut_off_start;
    public readonly ?string $cut_off_end;
    
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->additional_info = $data['additional_info'] ?? null;
        $this->cut_off_start = $data['cut_off_start'] ?? null;
        $this->cut_off_end = $data['cut_off_end'] ?? null;
    }
}

class ProductResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var ProductData[] */
    public readonly array $data;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->message = $data['message'] ?? '';
        $this->data = isset($data['data']) && is_array($data['data']) 
            ? array_map(fn ($item) => ProductData::from($item), $data['data'])
            : [];
    }
}

class ProductDetailResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?ProductDetailData $data;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->message = $data['message'] ?? '';
        $this->data = isset($data['data']) && is_array($data['data']) 
            ? ProductDetailData::from($data['data'])
            : null;
    }
}
