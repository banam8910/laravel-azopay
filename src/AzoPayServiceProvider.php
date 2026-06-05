<?php

namespace Ftech\AzoPay;

use Ftech\AzoPay\Client\AzoPayClient;
use Ftech\AzoPay\Webhook\WebhookHandler;
use Ftech\AzoPay\Webhook\WebhookSignature;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AzoPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/azopay.php', 'azopay');

        $this->app->singleton(AzoPayClient::class, function ($app) {
            return new AzoPayClient(
                $app->make(HttpFactory::class),
                $app['config']->get('azopay'),
            );
        });

        $this->app->singleton(WebhookSignature::class, function ($app) {
            $config = $app['config']->get('azopay.webhook');

            return WebhookSignature::make(
                $config['secrets'] ?? '',
                (int) ($config['tolerance'] ?? 300),
            );
        });

        $this->app->singleton(WebhookHandler::class, function ($app) {
            return new WebhookHandler(
                $app->make(Dispatcher::class),
                $app->make(Cache::class),
                (int) $app['config']->get('azopay.webhook.dedupe_ttl', 86400),
            );
        });

        $this->app->singleton('azopay', function ($app) {
            return new AzoPay(
                $app->make(AzoPayClient::class),
                $app['config']->get('azopay'),
            );
        });

        $this->app->alias('azopay', AzoPay::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/azopay.php' => $this->app->configPath('azopay.php'),
            ], 'azopay-config');
        }

        $this->registerWebhookRoute();
    }

    protected function registerWebhookRoute(): void
    {
        $route = $this->app['config']->get('azopay.webhook.route');

        if (! ($route['enabled'] ?? true)) {
            return;
        }

        Route::middleware($route['middleware'] ?? ['api'])
            ->group(__DIR__ . '/../routes/azopay.php');
    }
}
