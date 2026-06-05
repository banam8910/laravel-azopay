<?php

namespace Ftech\AzoPay\Events;

use Ftech\AzoPay\Data\Order;
use Ftech\AzoPay\Webhook\WebhookEvent;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired for every verified AzoPay webhook. A type-specific subclass
 * (e.g. {@see OrderPaid}) is dispatched as well so you can listen narrowly.
 */
class AzoPayWebhookReceived
{
    use Dispatchable;

    public function __construct(
        public readonly WebhookEvent $event,
    ) {
    }

    public function order(): ?Order
    {
        return $this->event->order();
    }

    public function type(): string
    {
        return $this->event->type;
    }
}
