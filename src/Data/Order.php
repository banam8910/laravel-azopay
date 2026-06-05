<?php

namespace Ftech\AzoPay\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An AzoPay order returned by POST /orders or GET /orders/{id}.
 *
 * @implements Arrayable<string, mixed>
 */
class Order implements Arrayable
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $status = null,
        public readonly ?int $amount = null,
        public readonly ?int $paidAmount = null,
        public readonly ?string $merchantOrderId = null,
        public readonly ?string $transferCode = null,
        public readonly ?string $checkoutToken = null,
        public readonly ?string $expiresAt = null,
        public readonly ?PaymentInfo $paymentInfo = null,
        public readonly array $metadata = [],
        public readonly array $raw = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            status: $data['status'] ?? null,
            amount: isset($data['amount']) ? (int) $data['amount'] : null,
            paidAmount: isset($data['paid_amount']) ? (int) $data['paid_amount'] : null,
            merchantOrderId: $data['merchant_order_id'] ?? null,
            transferCode: $data['transfer_code'] ?? null,
            checkoutToken: $data['checkout_token'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
            paymentInfo: isset($data['payment_info']) && is_array($data['payment_info'])
                ? PaymentInfo::fromArray($data['payment_info'])
                : null,
            metadata: $data['metadata'] ?? [],
            raw: $data,
        );
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function qrCodeUrl(): ?string
    {
        return $this->paymentInfo?->qrCodeUrl;
    }

    public function checkoutUrl(): ?string
    {
        return $this->paymentInfo?->checkoutUrl;
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
