<?php

namespace Ftech\AzoPay\Webhook;

use Ftech\AzoPay\Events\AzoPayWebhookReceived;
use Ftech\AzoPay\Events\OrderCancelled;
use Ftech\AzoPay\Events\OrderExpired;
use Ftech\AzoPay\Events\OrderOverpaid;
use Ftech\AzoPay\Events\OrderPaid;
use Ftech\AzoPay\Events\OrderUnderpaid;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Turns a verified webhook payload into Laravel events, with idempotency
 * based on the X-AzoPay-Event-Id header so retried deliveries are ignored.
 */
class WebhookHandler
{
    /**
     * Map AzoPay event types to their dedicated event classes.
     *
     * @var array<string, class-string<AzoPayWebhookReceived>>
     */
    protected const EVENT_MAP = [
        'order.paid'      => OrderPaid::class,
        'order.underpaid' => OrderUnderpaid::class,
        'order.overpaid'  => OrderOverpaid::class,
        'order.cancelled' => OrderCancelled::class,
        'order.expired'   => OrderExpired::class,
    ];

    public function __construct(
        protected Dispatcher $events,
        protected Cache $cache,
        protected int $dedupeTtl = 86400,
    ) {
    }

    /**
     * Dispatch events for a webhook payload. Returns false when the event was
     * a duplicate (already processed) and was therefore skipped.
     */
    public function handle(WebhookEvent $event): bool
    {
        if (! $this->markProcessed($event->id)) {
            return false;
        }

        // Always fire the generic event so a single listener can see everything.
        $this->events->dispatch(new AzoPayWebhookReceived($event));

        if ($class = self::EVENT_MAP[$event->type] ?? null) {
            $this->events->dispatch(new $class($event));
        }

        return true;
    }

    /**
     * Atomically record that we've seen this event id. Returns false if it was
     * already recorded (duplicate delivery).
     */
    protected function markProcessed(?string $eventId): bool
    {
        if ($eventId === null || $eventId === '') {
            return true;
        }

        return $this->cache->add('azopay_evt_' . md5($eventId), true, $this->dedupeTtl);
    }
}
