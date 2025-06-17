<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (php_sapi_name() === 'cli' && isset($_SERVER['argv']) && str_contains(implode(' ', $_SERVER['argv']), 'test')) {
    $_ENV['APP_ENV'] = 'testing'; // <- Laravel 11 uses this
    $_SERVER['APP_ENV'] = 'testing';
    putenv('APP_ENV=testing');

    $envFile = __DIR__.'/../.env.testing';
    if (file_exists($envFile)) {
        Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.testing')->load();
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verify.hmac' => \App\Http\Middleware\VerifyHmacSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
