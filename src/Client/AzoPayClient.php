<?php

namespace Ftech\AzoPay\Client;

use Ftech\AzoPay\Exceptions\ApiException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Thin HTTP client around the AzoPay REST API.
 *
 * Handles base URL resolution per environment, bearer auth, the /api/v1
 * prefix and turning non-2xx responses into {@see ApiException}.
 */
class AzoPayClient
{
    public function __construct(
        protected HttpFactory $http,
        protected array $config,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->send('GET', $endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->send('POST', $endpoint, $data);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->send('PATCH', $endpoint, $data);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->send('DELETE', $endpoint, $data);
    }

    /**
     * Send a request and return the decoded JSON body.
     *
     * @throws ApiException
     */
    public function send(string $method, string $endpoint, array $data = []): array
    {
        $response = $this->request()->send($method, $this->url($endpoint), [
            strtoupper($method) === 'GET' ? 'query' : 'json' => $data,
        ]);

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $this->decode($response);
    }

    protected function request(): PendingRequest
    {
        $request = $this->http
            ->asJson()
            ->acceptJson()
            ->withToken((string) ($this->config['api_key'] ?? ''))
            ->withHeaders(['User-Agent' => $this->userAgent()])
            ->timeout((int) ($this->config['timeout'] ?? 30));

        $retry = $this->config['retry'] ?? [];
        if (($retry['times'] ?? 0) > 1) {
            $request->retry((int) $retry['times'], (int) ($retry['sleep_ms'] ?? 200));
        }

        return $request;
    }

    protected function decode(Response $response): array
    {
        $body = $response->json();

        return is_array($body) ? $body : [];
    }

    protected function url(string $endpoint): string
    {
        $base = rtrim($this->baseUrl(), '/');
        $prefix = trim((string) ($this->config['api_prefix'] ?? 'api/v1'), '/');

        return "{$base}/{$prefix}/" . ltrim($endpoint, '/');
    }

    protected function baseUrl(): string
    {
        $env = $this->config['environment'] ?? 'sandbox';

        return $this->config['base_url'][$env]
            ?? $this->config['base_url']['sandbox']
            ?? 'https://staging-api.azopay.vn';
    }

    protected function userAgent(): string
    {
        return 'Laravel-AzoPay/1.0 (PHP/' . PHP_VERSION . ')';
    }

    public function isConnected(): bool
    {
        return ! empty($this->config['api_key']);
    }
}
