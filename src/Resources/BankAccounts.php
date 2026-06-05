<?php

namespace Ftech\AzoPay\Resources;

use Ftech\AzoPay\Client\AzoPayClient;
use Ftech\AzoPay\Data\BankAccount;
use Illuminate\Support\Collection;

class BankAccounts
{
    public function __construct(
        protected AzoPayClient $client,
    ) {
    }

    /**
     * List every bank account registered on the AzoPay dashboard.
     *
     * @return Collection<int, BankAccount>
     */
    public function all(): Collection
    {
        $response = $this->client->get('banks/accounts');
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return collect($data)->map(fn (array $account) => BankAccount::fromArray($account));
    }

    /**
     * Fetch a single bank account by id.
     */
    public function find(string $id): ?BankAccount
    {
        $response = $this->client->get("banks/accounts/{$id}");
        $data = $response['data'] ?? null;

        return is_array($data) ? BankAccount::fromArray($data) : null;
    }
}
