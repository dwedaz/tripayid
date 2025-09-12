<?php

namespace Tripay\PPOB\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tripay\PPOB\Services\ServerService server()
 * @method static \Tripay\PPOB\Services\BalanceService balance()
 * @method static \Tripay\PPOB\Services\PrepaidService prepaid()
 * @method static \Tripay\PPOB\Services\PostpaidService postpaid()
 * @method static \Tripay\PPOB\Services\TransactionService transaction()
 * @method static bool testConnection()
 * @method static float getBalance()
 * @method static \Tripay\PPOB\DTO\Response\TransactionResponse purchasePrepaid(string $productId, string $phoneNumber, string $apiTrxId, string $pin)
 * @method static \Tripay\PPOB\DTO\Response\BillCheckResponse checkBill(string $productId, string $phoneNumber, string $customerNumber, string $pin, ?string $apiTrxId = null)
 * @method static \Tripay\PPOB\DTO\Response\TransactionResponse payBill(int $trxId, string $apiTrxId, string $pin)
 *
 * @see \Tripay\PPOB\TripayManager
 */
class Tripay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tripay';
    }
}
