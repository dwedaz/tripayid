<?php

namespace Tripay\PPOB\Services;

abstract class BaseService
{
    protected TripayHttpClient $client;

    public function __construct(TripayHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Build query parameters for GET requests
     */
    protected function buildQueryParams(array $params): array
    {
        return array_filter($params, fn($value) => $value !== null);
    }

    /**
     * Build request payload for POST requests
     */
    protected function buildPayload(array $data): array
    {
        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Get endpoint URL for specific service
     */
    abstract protected function getEndpoint(string $action): string;

    /**
     * Make cached GET request
     */
    protected function getCachedData(string $cacheKey, string $endpoint, array $params = [], ?int $ttl = null): array
    {
        return $this->client->getCached($cacheKey, $endpoint, $params, $ttl);
    }

    /**
     * Get the HTTP client instance
     */
    public function getClient(): TripayHttpClient
    {
        return $this->client;
    }
}