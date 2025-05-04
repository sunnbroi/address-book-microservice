<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Js;
use SebastianBergmann\Type\MixedType;
class AuthController extends Controller
{
    private AuthService $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function login(LoginRequest $request): JsonResponse|MixedType
    {
        $data = $request->validated(); //проверка наличия client key и secret key в запросе

       
        $client = $this->authService->authenticateClient(
            $data['client_key'],
            $data['secret_key']
        );


        if (!$client->apiUser) {
            return response()->json(['message' => 'Client has no linked user'], 500);
        }
   

    $token = $client->apiUser->createToken('api-token')->plainTextToken;
    
    return response()->json([
        'token' => $token,
        'user' => $client->apiUser,
    ]);  // ответ клиенту по route api/login, если аутификация прошла успешно (сохранить токен и использовать в каждом запросе, через Authorization: token)
    }
    }
