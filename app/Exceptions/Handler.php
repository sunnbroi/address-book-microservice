<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function invalidJson($request, ValidationException $exception): JsonResponse
{
    \Log::error('🛑 Ошибка валидации', [
        'errors' => $exception->errors(),
    ]);

    return parent::invalidJson($request, $exception);
}

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        Log::error('🚨 Ошибка рендера', [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);

        return parent::render($request, $exception);
    }
}
