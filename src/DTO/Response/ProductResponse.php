<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class ProductData extends DataTransferObject
{
    public readonly string $id;
    public readonly string $name;
    public readonly ?string $code;
    public readonly ?string $category_id;
    public readonly ?string $operator_id;
    public readonly ?float $price;
    public readonly ?float $selling_price;
    public readonly ?string $description;
    public readonly ?bool $status;
    public readonly ?string $type; // prepaid or postpaid
    
    public function __construct(array $data = [])
    {
     
        $this->id = $data['id'] ?? '';
        $this->name = $data['product_name'] ?? '';
        $this->code = $data['code'] ?? null;
        $this->category_id = $data['pembeliankategori_id'] ?? null;
        $this->operator_id = $data['pembelianoperator_id'] ?? null;
        $this->price = isset($data['price']) ? (float)$data['price'] : null;
        $this->description = $data['desc'] ?? null;
        $this->status = (bool)$data['status'];
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
