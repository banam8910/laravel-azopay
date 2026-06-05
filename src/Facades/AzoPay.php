<?php

namespace Ftech\AzoPay\Facades;

use Ftech\AzoPay\Resources\BankAccounts;
use Ftech\AzoPay\Resources\Orders;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Orders orders()
 * @method static BankAccounts bankAccounts()
 * @method static \Ftech\AzoPay\Client\AzoPayClient client()
 * @method static \Ftech\AzoPay\Webhook\WebhookSignature signature()
 * @method static string remark(string $merchantOrderId, ?string $bankBin = null)
 * @method static bool isConnected()
 *
 * @see \Ftech\AzoPay\AzoPay
 */
class AzoPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'azopay';
    }
}
