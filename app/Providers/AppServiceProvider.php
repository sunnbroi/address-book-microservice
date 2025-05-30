<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TelegramService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Illuminate\Cache\RateLimiting\Limit;

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