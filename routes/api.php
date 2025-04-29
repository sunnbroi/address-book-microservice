<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::post('/messages', [MessageController::class, 'store']);

Route::get('/debug', function () {
    \Log::info('📢 Роут работает!');
    return 'OK';
});