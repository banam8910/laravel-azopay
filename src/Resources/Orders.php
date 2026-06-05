<?php

namespace Ftech\AzoPay\Resources;

use Ftech\AzoPay\Client\AzoPayClient;
use Ftech\AzoPay\Data\CreateOrderData;
use Ftech\AzoPay\Data\Order;
use Ftech\AzoPay\Exceptions\AzoPayException;

class Orders
{
    public function __construct(
        protected AzoPayClient $client,
        protected array $config,
    ) {
    }

    /**
     * Create a payment order and get back the QR code + transfer details.
     *
     * @param  CreateOrderData|array<string, mixed>  $data
     *
     * @throws AzoPayException
     */
    public function create(CreateOrderData|array $data): Order
    {
        $payload = $data instanceof CreateOrderData ? $data : $this->hydrate($data);

        $this->applyDefaults($payload);

        if ($payload->amount === null || $payload->amount <= 0) {
            throw new AzoPayException('An order amount greater than zero is required.');
        }

        if (empty($payload->merchantOrderId)) {
            throw new AzoPayException('A merchant_order_id is required to create an order.');
        }

        if (empty($payload->bankAccountId)) {
            throw new AzoPayException('No bank account configured. Set azopay.bank_account_id or call ->bankAccount().');
        }

        $response = $this->client->post('orders', $payload->toArray());

        return Order::fromArray($this->extractData($response));
    }

    /**
     * Fetch a single order by its AzoPay id.
     *
     * @throws AzoPayException
     */
    public function find(string $id): Order
    {
        $response = $this->client->get("orders/{$id}");

        return Order::fromArray($this->extractData($response));
    }

    protected function applyDefaults(CreateOrderData $payload): void
    {
        $payload->bankAccountId ??= $this->config['bank_account_id'] ?? null;
        $payload->expiresIn ??= (int) ($this->config['expires_in'] ?? 3600);

        if ($payload->expiresIn < 60) {
            $payload->expiresIn = 3600;
        }
    }

    protected function hydrate(array $data): CreateOrderData
    {
        return new CreateOrderData(
            amount: isset($data['amount']) ? (int) $data['amount'] : null,
            merchantOrderId: $data['merchant_order_id'] ?? null,
            bankAccountId: $data['bank_account_id'] ?? null,
            description: $data['description'] ?? null,
            orderType: $data['order_type'] ?? 'fixed',
            expiresIn: isset($data['expires_in']) ? (int) $data['expires_in'] : null,
            webhookUrl: $data['webhook_url'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * AzoPay wraps successful payloads in { status: "success", data: {...} }.
     *
     * @throws AzoPayException
     */
    protected function extractData(array $response): array
    {
        if (($response['status'] ?? null) === 'success' && is_array($response['data'] ?? null)) {
            return $response['data'];
        }

        if (is_array($response['data'] ?? null)) {
            return $response['data'];
        }

        throw new AzoPayException(
            $response['error_message'] ?? 'Unexpected response from AzoPay orders API.'
        );
    }
}
