<?php

namespace Ftech\AzoPay\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Fluent builder for the POST /orders payload.
 *
 * Only `amount` and `merchantOrderId` are strictly required; the bank account,
 * expiry and webhook URL fall back to config defaults inside the Orders
 * resource when omitted.
 *
 * @implements Arrayable<string, mixed>
 */
class CreateOrderData implements Arrayable
{
    public function __construct(
        public ?int $amount = null,
        public ?string $merchantOrderId = null,
        public ?string $bankAccountId = null,
        public ?string $description = null,
        public string $orderType = 'fixed',
        public ?int $expiresIn = null,
        public ?string $webhookUrl = null,
        public array $metadata = [],
    ) {
    }

    public static function make(): self
    {
        return new self();
    }

    public function amount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function merchantOrderId(string $id): self
    {
        $this->merchantOrderId = $id;

        return $this;
    }

    public function bankAccount(string $id): self
    {
        $this->bankAccountId = $id;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function expiresIn(int $seconds): self
    {
        $this->expiresIn = $seconds;

        return $this;
    }

    public function webhookUrl(string $url): self
    {
        $this->webhookUrl = $url;

        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'order_type'        => $this->orderType,
            'amount'            => $this->amount,
            'description'       => $this->description,
            'merchant_order_id' => $this->merchantOrderId,
            'bank_account_id'   => $this->bankAccountId,
            'expires_in'        => $this->expiresIn,
            'metadata'          => $this->metadata ?: null,
            'webhook_url'       => $this->webhookUrl,
        ], static fn ($value) => $value !== null);
    }
}
