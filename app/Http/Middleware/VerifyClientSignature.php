<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Client;
class VerifyClientSignature
{
    public function handle(Request $request, Closure $next)
    {
        $clientKey = $request->header('client_key');
        $token     = $request->header('token');

        if (!$clientKey || !$token) {
            throw new UnauthorizedHttpException('HMAC', 'Missing client_key or token');
        }

        $client = Client::where('client_key', $clientKey)->first();

        if (!$client) {
            throw new UnauthorizedHttpException('HMAC', 'Client not found');
        }

        $expected = hash_hmac('sha256', $request->getContent(), $client->secret_key);

        if (!hash_equals($expected, $token)) {
            throw new UnauthorizedHttpException('HMAC', 'Invalid token');
        }

        // Проброс авторизованного клиента в request
        $request->merge(['auth_client' => $client]);

        return response($request->getContent());
    }
}
