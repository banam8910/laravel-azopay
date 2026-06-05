<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Which AzoPay environment to talk to. Use "sandbox" for testing and
    | "live" for production. This selects the base URL below.
    |
    */

    'environment' => env('AZOPAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | The API key issued from your AzoPay dashboard. Sent as a Bearer token on
    | every API request.
    |
    */

    'api_key' => env('AZOPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URLs
    |--------------------------------------------------------------------------
    |
    | The REST API roots for each environment. The client appends the
    | "/api/v1/" path prefix automatically.
    |
    */

    'base_url' => [
        'sandbox' => env('AZOPAY_SANDBOX_URL', 'https://staging-api.azopay.vn'),
        'live'    => env('AZOPAY_LIVE_URL', 'https://my.azopay.vn'),
    ],

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
