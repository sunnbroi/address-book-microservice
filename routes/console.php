<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

RateLimiter::for('telegram:rate-limit', function () {
    return \Illuminate\Cache\RateLimiting\Limit::perSecond(50);
});
