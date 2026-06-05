<?php

namespace Ftech\AzoPay\Tests;

use Ftech\AzoPay\Exceptions\InvalidSignatureException;
use Ftech\AzoPay\Webhook\WebhookSignature;

class WebhookSignatureTest extends TestCase
{
    private function sign(string $secret, int $timestamp, string $body): string
    {
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

        return "t={$timestamp},v1={$signature}";
    }

    public function test_it_accepts_a_valid_signature(): void
    {
        $body = '{"type":"order.paid"}';
        $now = 1_700_000_000;
        $header = $this->sign('whsec_test', $now, $body);

        $signature = WebhookSignature::make('whsec_test', 300);

        $this->assertTrue($signature->isValid($body, $header, $now));
    }

    public function test_it_rejects_a_tampered_body(): void
    {
        $now = 1_700_000_000;
        $header = $this->sign('whsec_test', $now, '{"type":"order.paid"}');

        $signature = WebhookSignature::make('whsec_test', 300);

        $this->assertFalse($signature->isValid('{"type":"order.cancelled"}', $header, $now));
    }

    public function test_it_rejects_an_expired_timestamp(): void
    {
        $body = '{}';
        $signedAt = 1_700_000_000;
        $header = $this->sign('whsec_test', $signedAt, $body);

        $signature = WebhookSignature::make('whsec_test', 300);

        $this->assertFalse($signature->isValid($body, $header, $signedAt + 1_000));
    }

    public function test_it_supports_rotated_secrets(): void
    {
        $body = '{}';
        $now = 1_700_000_000;
        $header = $this->sign('whsec_old', $now, $body);

        $signature = WebhookSignature::make('whsec_new, whsec_old', 300);

        $this->assertTrue($signature->isValid($body, $header, $now));
    }

    public function test_it_throws_on_missing_header(): void
    {
        $this->expectException(InvalidSignatureException::class);

        WebhookSignature::make('whsec_test')->verify('{}', null);
    }
}
