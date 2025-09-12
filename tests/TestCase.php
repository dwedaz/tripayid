<?php

namespace Tripay\PPOB\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tripay\PPOB\TripayServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            TripayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('tripay.mode', 'sandbox');
        config()->set('tripay.api_key', 'test_api_key');
        config()->set('tripay.secret_pin', '1234');
        config()->set('tripay.sandbox_base_uri', 'https://tripay.id/api-sandbox/v2');
        config()->set('tripay.production_base_uri', 'https://tripay.id/api/v2');
        config()->set('tripay.timeout', 30);
        config()->set('tripay.retry', 3);
        config()->set('tripay.cache.enabled', false); // Disable caching for tests
        config()->set('tripay.logging.enabled', false); // Disable logging for tests
    }
}
