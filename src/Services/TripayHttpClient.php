<?php

namespace Tripay\PPOB\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Tripay\PPOB\Exceptions\ApiException;
use Tripay\PPOB\Exceptions\AuthenticationException;
use Tripay\PPOB\Exceptions\ValidationException;

class TripayHttpClient
{
    protected HttpFactory $http;
    protected array $config;
    protected string $baseUri;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->http = app(HttpFactory::class);
        $this->baseUri = $this->resolveBaseUri();

        $this->validateConfig();
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw AuthenticationException::missingApiKey();
        }

        if (empty($this->config['secret_pin'])) {
            throw AuthenticationException::missingSecretPin();
        }
    }

    protected function resolveBaseUri(): string
    {
        $mode = $this->config['mode'] ?? 'sandbox';
        
        return $mode === 'production' 
            ? $this->config['production_base_uri'] 
            : $this->config['sandbox_base_uri'];
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->makeRequest('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->makeRequest('POST', $endpoint, $data);
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUri . $endpoint;
        
        // Create HTTP client with default configuration
        $request = $this->http
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->config['timeout'] ?? 30)
            ->retry(
                $this->config['retry'] ?? 3, 
                $this->config['retry_delay'] ?? 1000
            );

        // Log request if enabled
        if ($this->shouldLogRequests()) {
            $this->logRequest($method, $url, $data);
        }

        try {
            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
            };

            // Log response if enabled
            if ($this->shouldLogResponses()) {
                $this->logResponse($response);
            }

            return $this->handleResponse($response);

        } catch (RequestException $e) {
            $this->logError($e);
            throw $this->transformException($e);
        }
    }

    protected function handleResponse(Response $response): array
    {
        $data = $response->json();

        // Handle Tripay's response structure
        if (isset($data['success']) && $data['success'] === false) {
            $message = $data['message'] ?? 'Unknown error occurred';
            
            // Handle specific error cases
            if (str_contains(strtolower($message), 'invalid api key')) {
                throw ApiException::invalidApiKey($message);
            }
            
            throw ApiException::badRequest($message, $data);
        }

        return $data;
    }

    protected function transformException(RequestException $e): \Exception
    {
        $response = $e->response;
        $statusCode = $response?->status();
        
        return match ($statusCode) {
            401 => AuthenticationException::invalidCredentials(),
            422 => ValidationException::make(
                $response->json()['errors'] ?? [], 
                'Validation failed'
            ),
            429 => ApiException::rateLimited(),
            500, 502, 503, 504 => ApiException::serverError(
                'Server error occurred', 
                $statusCode
            ),
            default => ApiException::networkError($e->getMessage())
        };
    }

    protected function shouldLogRequests(): bool
    {
        return $this->config['logging']['enabled'] ?? false 
            && $this->config['logging']['requests'] ?? false;
    }

    protected function shouldLogResponses(): bool
    {
        return $this->config['logging']['enabled'] ?? false 
            && $this->config['logging']['responses'] ?? false;
    }

    protected function logRequest(string $method, string $url, array $data): void
    {
        $channel = $this->config['logging']['channel'] ?? 'default';
        $level = $this->config['logging']['level'] ?? 'info';
        
        Log::channel($channel)->log($level, 'Tripay API Request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
        ]);
    }

    protected function logResponse(Response $response): void
    {
        $channel = $this->config['logging']['channel'] ?? 'default';
        $level = $this->config['logging']['level'] ?? 'info';
        
        Log::channel($channel)->log($level, 'Tripay API Response', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);
    }

    protected function logError(\Exception $e): void
    {
        $channel = $this->config['logging']['channel'] ?? 'default';
        
        Log::channel($channel)->error('Tripay API Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Get cached data or make a fresh request
     */
    public function getCached(string $cacheKey, string $endpoint, array $params = [], ?int $ttl = null): array
    {
        if (!$this->config['cache']['enabled']) {
            return $this->get($endpoint, $params);
        }

        $fullCacheKey = $this->config['cache']['prefix'] . ':' . $cacheKey;
        $ttl = $ttl ?? $this->config['cache']['ttl'];
        $store = $this->config['cache']['store'];

        return Cache::store($store)->remember($fullCacheKey, $ttl, function () use ($endpoint, $params) {
            return $this->get($endpoint, $params);
        });
    }

    /**
     * Clear cache by key pattern
     */
    public function clearCache(string $pattern = '*'): void
    {
        $prefix = $this->config['cache']['prefix'] . ':';
        $store = Cache::store($this->config['cache']['store']);
        
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar for efficiency
        if (method_exists($store, 'flush')) {
            $store->flush();
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }
}