<?php

namespace Tripay\PPOB\Tests\Unit;

use Tripay\PPOB\Services\BalanceService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\ServerService;
use Tripay\PPOB\Services\TransactionService;
use Tripay\PPOB\Services\TripayHttpClient;
use Tripay\PPOB\Tests\TestCase;
use Tripay\PPOB\TripayManager;

class TripayServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_tripay_manager_in_container(): void
    {
        $manager = $this->app->make('tripay');

        $this->assertInstanceOf(TripayManager::class, $manager);
    }

    /** @test */
    public function it_registers_http_client_in_container(): void
    {
        $client = $this->app->make(TripayHttpClient::class);

        $this->assertInstanceOf(TripayHttpClient::class, $client);
    }

    /** @test */
    public function it_registers_all_services_in_container(): void
    {
        $this->assertInstanceOf(ServerService::class, $this->app->make(ServerService::class));
        $this->assertInstanceOf(BalanceService::class, $this->app->make(BalanceService::class));
        $this->assertInstanceOf(PrepaidService::class, $this->app->make(PrepaidService::class));
        $this->assertInstanceOf(PostpaidService::class, $this->app->make(PostpaidService::class));
        $this->assertInstanceOf(TransactionService::class, $this->app->make(TransactionService::class));
    }

    /** @test */
    public function manager_provides_access_to_all_services(): void
    {
        $manager = $this->app->make('tripay');

        $this->assertInstanceOf(ServerService::class, $manager->server());
        $this->assertInstanceOf(BalanceService::class, $manager->balance());
        $this->assertInstanceOf(PrepaidService::class, $manager->prepaid());
        $this->assertInstanceOf(PostpaidService::class, $manager->postpaid());
        $this->assertInstanceOf(TransactionService::class, $manager->transaction());
    }
}
