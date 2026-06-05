<?php

namespace Ftech\AzoPay\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The bank-transfer details a customer needs to complete a payment:
 * recipient account, bank, the VietQR image and a hosted checkout URL.
 *
 * @implements Arrayable<string, mixed>
 */
class PaymentInfo implements Arrayable
{
    public function __construct(
        public readonly ?string $accountNumber = null,
        public readonly ?string $accountName = null,
        public readonly ?string $bank = null,
        public readonly ?string $qrCodeUrl = null,
        public readonly ?string $checkoutUrl = null,
        public readonly array $raw = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: $data['account_number'] ?? null,
            accountName: $data['account_name'] ?? null,
            bank: $data['bank'] ?? null,
            qrCodeUrl: $data['qr_code_url'] ?? null,
            checkoutUrl: $data['checkout_url'] ?? null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'account_name'   => $this->accountName,
            'bank'           => $this->bank,
            'qr_code_url'    => $this->qrCodeUrl,
            'checkout_url'   => $this->checkoutUrl,
        ];
    }
}
