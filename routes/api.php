<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AddressBookController;

Route::post('/messages', [MessageController::class, 'store']);

Route::get('/debug', function () {
    \Log::info('📢 Роут работает!');
    return 'OK';
});
Route::get('/ping', fn() => 'pong');

Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifyClientSignature::class])->group(function () {
    Route::apiResource('address-books', AddressBookController::class);
});