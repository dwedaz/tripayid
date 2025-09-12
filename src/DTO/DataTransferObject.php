<?php

namespace Tripay\PPOB\DTO;

use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject
{
    public function __construct(array $data = [])
    {
        $this->fillFromArray($data);
    }

    /**
     * Create instance from array
     */
    public static function from(array $data): static
    {
        return new static($data);
    }

    /**
     * Fill object properties from array
     */
    protected function fillFromArray(array $data): void
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            
            if (isset($data[$propertyName])) {
                $value = $data[$propertyName];
                
                // Handle nested DTOs
                if ($property->hasType()) {
                    $type = $property->getType();
                    if ($type && !$type->isBuiltin()) {
                        $typeName = $type->getName();
                        if (is_subclass_of($typeName, self::class)) {
                            $value = is_array($value) ? $typeName::from($value) : $value;
                        }
                    }
                }
                
                $this->{$propertyName} = $value;
            }
        }
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $value = $this->{$propertyName} ?? null;

            if ($value instanceof self) {
                $value = $value->toArray();
            } elseif (is_array($value)) {
                $value = array_map(function ($item) {
                    return $item instanceof self ? $item->toArray() : $item;
                }, $value);
            }

            $result[$propertyName] = $value;
        }

        return array_filter($result, fn($value) => $value !== null);
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Get only the properties that have values
     */
    public function only(array $properties): array
    {
        $data = $this->toArray();
        return array_intersect_key($data, array_flip($properties));
    }

    /**
     * Get all properties except specified ones
     */
    public function except(array $properties): array
    {
        $data = $this->toArray();
        return array_diff_key($data, array_flip($properties));
    }
}