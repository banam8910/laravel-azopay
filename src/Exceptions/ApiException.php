<?php

namespace Ftech\AzoPay\Exceptions;

use Illuminate\Http\Client\Response;

class ApiException extends AzoPayException
{
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly ?array $errors = null,
        public readonly ?Response $response = null,
    ) {
        parent::__construct($message, $status);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();

        $message = is_array($body)
            ? ($body['error_message'] ?? $body['message'] ?? 'AzoPay API request failed.')
            : 'AzoPay API request failed.';

        $errors = is_array($body) ? ($body['errors'] ?? null) : null;

        return new self($message, $response->status(), $errors, $response);
    }
}
