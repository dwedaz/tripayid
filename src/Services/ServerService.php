<?php

namespace Tripay\PPOB\Services;

use Tripay\PPOB\DTO\Response\ServerResponse;

class ServerService extends BaseService
{
    /**
     * Check server status
     */
    public function checkServer(): ServerResponse
    {
        $response = $this->client->get($this->getEndpoint('check'));

        return ServerResponse::from($response);
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->checkServer();

            return $response->success;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getEndpoint(string $action): string
    {
        return match ($action) {
            'check' => '/cekserver',
            default => throw new \InvalidArgumentException("Unknown action: $action")
        };
    }
}
