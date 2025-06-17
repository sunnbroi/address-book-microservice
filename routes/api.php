<?php

use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TelegramAddressBookController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verify.hmac'])->group(function () {

    Route::apiResource('address-books', AddressBookController::class); // crud address books
    Route::apiResource('recipients', RecipientController::class); // crud recipients

    Route::get('address-books/{addressBook}', [RecipientController::class, 'show']);
    Route::post('address-books/{addressBook}', [RecipientController::class, 'store']);
    Route::delete('/address-books/{addressBook}/{recepient}', [RecipientController::class, 'delete']);
    Route::post('/telegram/send-message', [TelegramAddressBookController::class, 'sendMessage']);
});

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
