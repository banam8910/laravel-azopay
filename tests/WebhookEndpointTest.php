<?php

namespace Ftech\AzoPay\Tests;

use Ftech\AzoPay\Events\AzoPayWebhookReceived;
use Ftech\AzoPay\Events\OrderPaid;
use Illuminate\Support\Facades\Event;

class WebhookEndpointTest extends TestCase
{
    private function payload(): string
    {
        return json_encode([
            'type' => 'order.paid',
            'data' => [
                'order' => [
                    'merchant_order_id' => 'DH55',
                    'paid_amount' => 100_000,
                    'status' => 'paid',
                ],
            ],
        ]);
    }

    private function sign(string $body, int $timestamp): string
    {
        $sig = hash_hmac('sha256', "{$timestamp}.{$body}", 'whsec_test');

        return "t={$timestamp},v1={$sig}";
    }

    public function test_valid_webhook_dispatches_events(): void
    {
        Event::fake([AzoPayWebhookReceived::class, OrderPaid::class]);

        $body = $this->payload();
        $header = $this->sign($body, time());

        $response = $this->call(
            'POST',
            '/azopay/webhook',
            [],
            [],
            [],
            ['HTTP_X-AzoPay-Signature' => $header, 'HTTP_X-AzoPay-Event-Id' => 'evt_1', 'CONTENT_TYPE' => 'application/json'],
            $body,
        );

        $response->assertOk()->assertJson(['received' => true, 'duplicate' => false]);

        Event::assertDispatched(OrderPaid::class, function (OrderPaid $e) {
            return $e->order()->merchantOrderId === 'DH55'
                && $e->order()->paidAmount === 100_000;
        });
        Event::assertDispatched(AzoPayWebhookReceived::class);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $body = $this->payload();

        $response = $this->call(
            'POST',
            '/azopay/webhook',
            [],
            [],
            [],
            ['HTTP_X-AzoPay-Signature' => 't=1,v1=deadbeef', 'CONTENT_TYPE' => 'application/json'],
            $body,
        );

        $response->assertStatus(400);
    }

    public function test_duplicate_event_is_not_reprocessed(): void
    {
        $body = $this->payload();
        $header = $this->sign($body, time());
        $server = ['HTTP_X-AzoPay-Signature' => $header, 'HTTP_X-AzoPay-Event-Id' => 'evt_dup', 'CONTENT_TYPE' => 'application/json'];

        $this->call('POST', '/azopay/webhook', [], [], [], $server, $body)->assertOk();

        $second = $this->call('POST', '/azopay/webhook', [], [], [], $server, $body);
        $second->assertOk()->assertJson(['duplicate' => true]);
    }
}
