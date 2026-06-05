<?php

use Ftech\AzoPay\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post(
    config('azopay.webhook.route.path', 'azopay/webhook'),
    WebhookController::class,
)->name(config('azopay.webhook.route.name', 'azopay.webhook'));
