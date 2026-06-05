<?php

namespace Ftech\AzoPay\Data;

use Ftech\AzoPay\Support\Banks;
use Illuminate\Contracts\Support\Arrayable;

/**
 * A bank account registered on the merchant's AzoPay dashboard, used to
 * receive transfers. Returned by GET /banks/accounts[/{id}].
 *
 * @implements Arrayable<string, mixed>
 */
class BankAccount implements Arrayable
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $accountNumber = null,
        public readonly ?string $accountHolderName = null,
        public readonly ?string $bankCode = null,
        public readonly ?string $bankShortName = null,
        public readonly ?string $bankFullName = null,
        public readonly ?string $bankBin = null,
        public readonly array $raw = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $bank = is_array($data['bank'] ?? null) ? $data['bank'] : [];
        $code = $data['bank_code'] ?? null;
        $static = $code ? Banks::find((string) $code) : null;

        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            accountNumber: $data['account_number'] ?? null,
            accountHolderName: $data['account_holder_name'] ?? null,
            bankCode: $code,
            bankShortName: $bank['short_name'] ?? $static['short_name'] ?? $code,
            bankFullName: $bank['full_name'] ?? $data['bank_name'] ?? $static['full_name'] ?? null,
            bankBin: $bank['bin'] ?? $static['bin'] ?? null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'account_number'      => $this->accountNumber,
            'account_holder_name' => $this->accountHolderName,
            'bank_code'           => $this->bankCode,
            'bank_short_name'     => $this->bankShortName,
            'bank_full_name'      => $this->bankFullName,
            'bank_bin'            => $this->bankBin,
        ];
    }
}
