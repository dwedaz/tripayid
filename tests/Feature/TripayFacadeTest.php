<?php

namespace Tripay\PPOB\Tests\Feature;

use Tripay\PPOB\Facades\Tripay;
use Tripay\PPOB\Services\BalanceService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\ServerService;
use Tripay\PPOB\Services\TransactionService;
use Tripay\PPOB\Tests\TestCase;

class TripayFacadeTest extends TestCase
{
    /** @test */
    public function facade_returns_server_service(): void
    {
        $service = Tripay::server();

        $this->assertInstanceOf(ServerService::class, $service);
    }

    /** @test */
    public function facade_returns_balance_service(): void
    {
        $service = Tripay::balance();

        $this->assertInstanceOf(BalanceService::class, $service);
    }

    /** @test */
    public function facade_returns_prepaid_service(): void
    {
        $service = Tripay::prepaid();

        $this->assertInstanceOf(PrepaidService::class, $service);
    }

    /** @test */
    public function facade_returns_postpaid_service(): void
    {
        $service = Tripay::postpaid();

        $this->assertInstanceOf(PostpaidService::class, $service);
    }

    /** @test */
    public function facade_returns_transaction_service(): void
    {
        $service = Tripay::transaction();

        $this->assertInstanceOf(TransactionService::class, $service);
    }
}
