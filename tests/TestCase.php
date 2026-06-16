<?php

namespace Ftech\AzoPay\Tests;

use Ftech\AzoPay\AzoPayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [AzoPayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('azopay.api_url', 'https://app.azopay.vn');
        $app['config']->set('azopay.api_key', 'test-key');
        $app['config']->set('azopay.bank_account_id', '42');
        $app['config']->set('azopay.webhook.secrets', 'whsec_test');
        $app['config']->set('cache.default', 'array');
    }
}
