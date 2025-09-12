<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class ProductData extends DataTransferObject
{
    public readonly string $product_id;
    public readonly string $product_name;
    public readonly ?string $category;
    public readonly ?string $operator;
    public readonly ?float $price;
    public readonly ?float $selling_price;
    public readonly ?string $description;
    public readonly ?bool $status;
    public readonly ?string $type; // prepaid or postpaid
}

class ProductDetailData extends ProductData
{
    public readonly ?array $additional_info;
    public readonly ?string $cut_off_start;
    public readonly ?string $cut_off_end;
}

class ProductResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var ProductData[] */
    public readonly array $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);
        
        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = array_map(
                fn($item) => ProductData::from($item), 
                $data['data']
            );
        }
    }
}

class ProductDetailResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    public readonly ?ProductDetailData $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);
        
        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = ProductDetailData::from($data['data']);
        }
    }
}