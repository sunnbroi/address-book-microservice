<?php

namespace App\Services;

use App\Models\Client;

class AuthService
{
    public function authenticateClient(string $clientKey, string $secretKey): ?Client
    {
        
        $client = Client::with(relations: 'apiUser')->where('client_key', $clientKey)->first(); // поисе записи по client_key

        if (!$client || !hash_equals($client->secret_key, $secretKey)) {
            return null;
        }

        return $client;
    }
}
