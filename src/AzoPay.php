<?php

namespace Ftech\AzoPay;

use Ftech\AzoPay\Client\AzoPayClient;
use Ftech\AzoPay\Resources\BankAccounts;
use Ftech\AzoPay\Resources\Orders;
use Ftech\AzoPay\Support\Banks;
use Ftech\AzoPay\Webhook\WebhookSignature;

/**
 * Entry point for the AzoPay API. Resolve via the AzoPay facade or the
 * container binding "azopay".
 */
class AzoPay
{
    public function __construct(
        protected AzoPayClient $client,
        protected array $config,
    ) {
    }

    public function orders(): Orders
    {
        return new Orders($this->client, $this->config);
    }

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this->client);
    }

    public function client(): AzoPayClient
    {
        return $this->client;
    }

    public function signature(): WebhookSignature
    {
        return WebhookSignature::make(
            $this->config['webhook']['secrets'] ?? '',
            (int) ($this->config['webhook']['tolerance'] ?? 300),
        );
    }

    /**
     * Build the bank-transfer remark/content for a merchant order id, adding
     * the SEVQR prefix required by VietinBank and ABBANK.
     */
    public function remark(string $merchantOrderId, ?string $bankBin = null): string
    {
        return Banks::requiresSevqrPrefix($bankBin)
            ? "SEVQR {$merchantOrderId}"
            : $merchantOrderId;
    }

    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }
}
