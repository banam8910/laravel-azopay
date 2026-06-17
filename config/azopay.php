<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | The API key issued from your AzoPay dashboard. Sent as a Bearer token on
    | every API request. The server determines whether the key is sandbox or
    | live, so there is no environment flag to configure here.
    |
    */

    'api_key' => env('AZOPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The REST API root. The client appends the "/api/v1/" path prefix
    | automatically. Both live and sandbox use the same URL; only the API key
    | differs.
    |
    */

    'api_url' => env('AZOPAY_API_URL', 'https://api.azopay.vn'),

    /*
    |--------------------------------------------------------------------------
    | API version prefix
    |--------------------------------------------------------------------------
    */

    'api_prefix' => 'api/v1',

    /*
    |--------------------------------------------------------------------------
    | HTTP options
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('AZOPAY_TIMEOUT', 30),
    'retry' => [
        'times'    => (int) env('AZOPAY_RETRY_TIMES', 1),
        'sleep_ms' => (int) env('AZOPAY_RETRY_SLEEP', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order defaults
    |--------------------------------------------------------------------------
    |
    | Default bank account used to receive payments, the payment code prefix
    | and how long (in seconds) a generated QR code stays valid.
    |
    */

    'bank_account_id' => env('AZOPAY_BANK_ACCOUNT_ID'),
    'pay_code_prefix' => env('AZOPAY_PAY_CODE_PREFIX', 'DH'),
    'expires_in'      => (int) env('AZOPAY_EXPIRES_IN', 3600),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | "secrets" accepts one or more signing secrets (comma separated) so you
    | can rotate keys without downtime. "tolerance" is the maximum clock skew
    | (seconds) accepted on the signature timestamp. The route options control
    | the auto-registered webhook endpoint.
    |
    */

    'webhook' => [
        'secrets'   => env('AZOPAY_WEBHOOK_SECRET'),
        'tolerance' => (int) env('AZOPAY_WEBHOOK_TOLERANCE', 300),

        'route' => [
            'enabled'    => env('AZOPAY_WEBHOOK_ROUTE', true),
            'path'       => env('AZOPAY_WEBHOOK_PATH', 'azopay/webhook'),
            'middleware' => ['api'],
            'name'       => 'azopay.webhook',
        ],

        // How long processed event ids are remembered for idempotency.
        'dedupe_ttl' => (int) env('AZOPAY_WEBHOOK_DEDUPE_TTL', 86400),
    ],

];
