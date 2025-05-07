<?php 
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TelegramTestController;

Route::post('/login', [AuthController::class, 'login']); //запрос от клиента на проверку аутифекации через sanctum

Route::apiResource('address-books', AddressBookController::class);// crud address books
Route::apiResource('recipients', RecipientController::class); // crud recipients

Route::post('address-books/{addressBook}/attach', [AddressBookController::class, 'attach']);
Route::post('address-books/{addressBook}/detach', [AddressBookController::class, 'detach']);
Route::post('address-books/{addressBook}/sync', [AddressBookController::class, 'sync']);
Route::post('address-books/bulk-store', [AddressBookController::class, 'bulkStore']);

Route::post('recipients/{recipient}/attach', [RecipientController::class, 'attach']);
Route::post('recipients/{recipient}/detach', [RecipientController::class, 'detach']);
Route::post('recipients/{recipient}/sync', [RecipientController::class, 'sync']);
Route::post('recipients/bulk-store', [RecipientController::class, 'bulkStore']);

Route::post('/telegram/send-message', [TelegramTestController::class, 'sendTelegramMessage']);