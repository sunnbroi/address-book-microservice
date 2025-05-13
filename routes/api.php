<?php 
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TelegramAddressBookController;

Route::middleware([/*'auth:sanctum', */'verify.hmac'])->group(function (){
    Route::post('/login', [AuthController::class, 'login']); //получение токена

    Route::apiResource('address-books', AddressBookController::class);// crud address books
    Route::apiResource('recipients', RecipientController::class); // crud recipients

    Route::post('address-books/{addressBook}/attach', [AddressBookController::class, 'attach']);
    Route::post('address-books/{addressBook}/detach', [AddressBookController::class, 'detach']);
    Route::post('address-books/{addressBook}/sync', [AddressBookController::class, 'sync']);
    Route::post('address-books/bulk-store', [AddressBookController::class, 'bulkStore']);

    Route::get('recipients/{addressBook}', [RecipientController::class, 'show']);
    Route::post('recipients/{addressBook}' , [RecipientController::class, 'store']);
    Route::post('recipients/{recipient}/attach', [RecipientController::class, 'attach']);
    Route::post('recipients/{recipient}/detach', [RecipientController::class, 'detach']);
    Route::post('recipients/{recipient}/sync', [RecipientController::class, 'sync']);
    Route::post('recipients/bulk-store', [RecipientController::class, 'bulkStore']);

    // Route::post('/telegram/send-personal-message', [TelegramService::class, 'sendMessage']);
    Route::post('/telegram/send-message', [TelegramAddressBookController::class, 'sendMessage']);

});