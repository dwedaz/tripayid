<?php

namespace Tripay\PPOB\Exceptions;

class ValidationException extends TripayException
{
    protected array $errors = [];

    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public static function make(array $errors, string $message = 'Validation failed'): static
    {
        return new static($message, $errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function getError(string $field, $default = null)
    {
        return $this->errors[$field] ?? $default;
    }
}