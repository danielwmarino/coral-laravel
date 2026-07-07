<?php

namespace App\Providers;

use Anthropic\Client as AnthropicClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnthropicClient::class, function () {
            return new AnthropicClient(apiKey: config('services.anthropic.key'));
        });
    }

    public function boot(): void {}
}
