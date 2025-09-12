<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class OperatorData extends DataTransferObject
{
    public readonly int $id;
    public readonly string $code;
    public readonly ?string $name;
    public readonly ?string $category_id;
    public readonly bool $status;

    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->code = $data['product_id'] ?? '';
        $this->name = $data['product_name'] ?? null;
        $this->category_id = $data['pembeliankategori_id'] ?? null;
        $this->status = $data['status'] ?? null;
    }
}

class OperatorResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
    /** @var OperatorData[] */
    public readonly array $data;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->message = $data['message'] ?? '';
        $this->data = isset($data['data']) && is_array($data['data']) 
            ? array_map(fn ($item) => OperatorData::from($item), $data['data'])
            : [];
    }
}
