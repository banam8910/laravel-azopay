<?php

namespace Ftech\AzoPay\Http\Controllers;

use Ftech\AzoPay\Exceptions\InvalidSignatureException;
use Ftech\AzoPay\Webhook\WebhookEvent;
use Ftech\AzoPay\Webhook\WebhookHandler;
use Ftech\AzoPay\Webhook\WebhookSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Default endpoint that receives, verifies and dispatches AzoPay webhooks.
 *
 * Registered automatically at the configured path unless you disable the
 * route in config and wire up your own handling.
 */
class WebhookController
{
    public function __construct(
        protected WebhookSignature $signature,
        protected WebhookHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $body = $request->getContent();

        try {
            $this->signature->verify($body, $request->header('X-AzoPay-Signature'));
        } catch (InvalidSignatureException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $payload = json_decode($body, true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid_payload'], 400);
        }

        $event = WebhookEvent::fromArray(
            $payload,
            $request->header('X-AzoPay-Event-Id'),
        );

        $fresh = $this->handler->handle($event);

        return response()->json(['received' => true, 'duplicate' => ! $fresh]);
    }
}
