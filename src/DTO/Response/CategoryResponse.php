<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class CategoryData extends DataTransferObject
{
    public readonly string $id;
    public readonly string $name;
    public readonly ?string $type;
    public readonly bool $status;

    public function __construct(array $data = [])
    {
      
        $this->id =  $data['product_id'] ?? $data['id'] ?? '';
        $this->name =$data['product_name'] ??   $data['name'] ?? '';
        $this->type = $data['type'] ?? null;
        $this->status = isset($data['status']) ? (bool)$data['status'] : false;
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
