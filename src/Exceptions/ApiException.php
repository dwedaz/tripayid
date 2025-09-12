<?php

namespace Tripay\PPOB\Exceptions;

class ApiException extends TripayException
{
    public static function invalidApiKey(string $message = 'Invalid API Key'): static
    {
        return new static($message, 401);
    }

    public static function serverError(string $message = 'Server Error', int $code = 500): static
    {
        return new static($message, $code);
    }

    public static function badRequest(string $message = 'Bad Request', array $context = []): static
    {
        return new static($message, 400, null, $context);
    }

    public static function rateLimited(string $message = 'Rate limit exceeded'): static
    {
        return new static($message, 429);
    }

    public static function networkError(string $message = 'Network error occurred'): static
    {
        return new static($message, 0);
    }
}