<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class CategoryData extends DataTransferObject
{
    public readonly string $product_id;
    public readonly string $product_name;
    public readonly ?string $description;
}

class CategoryResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var CategoryData[] */
    public readonly array $data;

    protected function fillFromArray(array $data): void
    {
        parent::fillFromArray($data);
        
        if (isset($data['data']) && is_array($data['data'])) {
            $this->data = array_map(
                fn($item) => CategoryData::from($item), 
                $data['data']
            );
        }
    }
}