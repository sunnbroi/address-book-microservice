<?php 
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']); //запрос от клиента на проверку аутифекации через sanctum
