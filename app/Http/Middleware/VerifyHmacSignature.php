<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;

class VerifyHmacSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientKey = $request->header('X-Client-Key');
        $signature = $request->header('X-Signature');
        
        if (!$clientKey && !$signature) {
            return response()->json(['message' => 'Missing headers: X-Client-Key and X-Signature'], 401);
        }

        if (!$clientKey) {
            return response()->json(['message' => 'Missing header: X-Client-Key'], 401);
        }

        if (!$signature) {
            return response()->json(['message' => 'Missing header: X-Signature'], 401);
        }

        // Проверка клиента
        $client = Client::where('client_key', $clientKey)->first();

        if (!$client) {
            return response()->json(['message' => 'Invalid client key'], 401);
        }

        // Проверка подписи
        $body = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $body, $client->secret_key);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
