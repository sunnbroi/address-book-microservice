<?php

namespace App\Providers;

use App\Services\TelegramService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TelegramService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(RateLimiter $rateLimiter)
    {
        app(RateLimiter::class)->for('telegram-batch', function () {
            return Limit::perSecond(1)->by('global-telegram-batch');
        });
    }
}
