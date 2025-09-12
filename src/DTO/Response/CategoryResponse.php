<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class CategoryData extends DataTransferObject
{
    public readonly string $category_id;
    public readonly string $category_name;
    public readonly ?string $description;
    
    public function __construct(array $data = [])
    {
        $this->category_id = $data['category_id'] ?? $data['product_id'] ?? '';
        $this->category_name = $data['category_name'] ?? $data['product_name'] ?? '';
        $this->description = $data['description'] ?? null;
    }
}

class CategoryResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var CategoryData[] */
    public readonly array $data;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->message = $data['message'] ?? '';
        $this->data = isset($data['data']) && is_array($data['data']) 
            ? array_map(fn ($item) => CategoryData::from($item), $data['data'])
            : [];
    }
}
