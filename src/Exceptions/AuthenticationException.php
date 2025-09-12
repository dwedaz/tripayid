<?php

namespace Tripay\PPOB\Exceptions;

class AuthenticationException extends TripayException
{
    public static function invalidCredentials(): static
    {
        return new static('Invalid API credentials provided', 401);
    }

    public static function missingApiKey(): static
    {
        return new static('API key is required but not provided', 401);
    }

    public static function missingSecretPin(): static
    {
        return new static('Secret PIN is required but not provided', 401);
    }

    public static function invalidSignature(): static
    {
        return new static('Invalid webhook signature', 401);
    }
}