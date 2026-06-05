<?php

namespace Ftech\AzoPay\Webhook;

use Ftech\AzoPay\Exceptions\InvalidSignatureException;

/**
 * Verifies the `X-AzoPay-Signature` header on incoming webhooks.
 *
 * The header has the form `t=<unix_ts>,v1=<hex_hmac>[,v1=<hex_hmac>...]`. The
 * signed payload is `"{timestamp}.{raw_body}"`, hashed with HMAC-SHA256 using
 * the shared webhook secret. Multiple `v1` signatures and multiple configured
 * secrets are supported to allow key rotation.
 */
class WebhookSignature
{
    /**
     * @param  array<int, string>  $secrets
     */
    public function __construct(
        protected array $secrets,
        protected int $tolerance = 300,
    ) {
    }

    /**
     * @param  string|array<int, string>  $secrets
     */
    public static function make(string|array $secrets, int $tolerance = 300): self
    {
        return new self(self::normalizeSecrets($secrets), $tolerance);
    }

    /**
     * Returns true when the signature is valid, false otherwise.
     */
    public function isValid(string $payload, ?string $header, ?int $now = null): bool
    {
        try {
            $this->verify($payload, $header, $now);

            return true;
        } catch (InvalidSignatureException) {
            return false;
        }
    }

    /**
     * Verify the signature, throwing on any failure.
     *
     * @throws InvalidSignatureException
     */
    public function verify(string $payload, ?string $header, ?int $now = null): void
    {
        if ($header === null || trim($header) === '') {
            throw InvalidSignatureException::reason('missing_signature_header');
        }

        if (empty($this->secrets)) {
            throw InvalidSignatureException::reason('no_secret_configured');
        }

        $parsed = $this->parse($header);
        if ($parsed === null) {
            throw InvalidSignatureException::reason('malformed_signature_header');
        }

        $now ??= time();
        if (abs($now - $parsed['timestamp']) > $this->tolerance) {
            throw InvalidSignatureException::reason('timestamp_out_of_tolerance');
        }

        $signed = $parsed['timestamp'] . '.' . $payload;

        foreach ($this->secrets as $secret) {
            $expected = hash_hmac('sha256', $signed, $secret);
            foreach ($parsed['signatures'] as $provided) {
                if (hash_equals($expected, $provided)) {
                    return;
                }
            }
        }

        throw InvalidSignatureException::reason('signature_mismatch');
    }

    /**
     * @return array{timestamp: int, signatures: array<int, string>}|null
     */
    protected function parse(string $header): ?array
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $part) {
            $trimmed = trim($part);
            $eq = strpos($trimmed, '=');
            if ($eq === false) {
                continue;
            }

            $key = substr($trimmed, 0, $eq);
            $value = substr($trimmed, $eq + 1);

            if ($key === 't') {
                if (! preg_match('/^-?\d+$/', $value)) {
                    return null;
                }
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || $signatures === []) {
            return null;
        }

        return ['timestamp' => $timestamp, 'signatures' => $signatures];
    }

    /**
     * @param  string|array<int, string>  $secrets
     * @return array<int, string>
     */
    protected static function normalizeSecrets(string|array $secrets): array
    {
        if (is_string($secrets)) {
            $secrets = preg_split('/[\s,]+/', $secrets) ?: [];
        }

        return array_values(array_filter(array_map('trim', $secrets), 'strlen'));
    }
}
