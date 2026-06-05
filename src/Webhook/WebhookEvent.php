<?php

namespace Ftech\AzoPay\Webhook;

use Ftech\AzoPay\Data\Order;
use Illuminate\Contracts\Support\Arrayable;

/**
 * A decoded webhook event from AzoPay.
 *
 * Payload shape: { type: "order.paid", data: { order: { ... } } }.
 *
 * @implements Arrayable<string, mixed>
 */
class WebhookEvent implements Arrayable
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $id,
        public readonly array $payload,
    ) {
    }

    public static function fromArray(array $payload, ?string $eventId = null): self
    {
        return new self(
            type: (string) ($payload['type'] ?? ''),
            id: $eventId ?? ($payload['id'] ?? null),
            payload: $payload,
        );
    }

    public function data(): array
    {
        return is_array($this->payload['data'] ?? null) ? $this->payload['data'] : [];
    }

    /**
     * The order embedded in the event, if any.
     */
    public function order(): ?Order
    {
        $order = $this->data()['order'] ?? null;

        return is_array($order) ? Order::fromArray($order) : null;
    }

    public function merchantOrderId(): ?string
    {
        return $this->order()?->merchantOrderId;
    }

    public function isOrderEvent(): bool
    {
        return str_starts_with($this->type, 'order.');
    }

    public function toArray(): array
    {
        return $this->payload;
    }
}
