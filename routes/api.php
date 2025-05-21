<?php 
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TelegramAddressBookController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\MessageStatusController;

    Route::middleware(['verify.hmac'])->group(function (){
        
        Route::apiResource('address-books', AddressBookController::class);// crud address books
        Route::apiResource('recipients', RecipientController::class); // crud recipients

        Route::get('address_books/{addressBook}', [RecipientController::class, 'show']);
        Route::post('address_books/{addressBook}' , [RecipientController::class, 'store']);
        Route::delete('/address_books/{bookID}/{recepient}' , [RecipientController::class, 'detach']);

        
        Route::post('/telegram/send-message', [TelegramAddressBookController::class, 'sendMessage']);
        Route::post('/messages', [MessageController::class, 'store']);
        
        Route::get('/messages/{id}', [MessageStatusController::class, 'show']);
        
    });
    
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);