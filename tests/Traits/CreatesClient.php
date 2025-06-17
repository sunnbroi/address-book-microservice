<?php

namespace Tests\Traits;

use App\Models\Client;

trait CreatesClient
{
    protected Client $client;

    protected string $clientKey;

    protected string $secretKey;

    protected function createTestClient(): void
    {
        $this->client = Client::factory()->create();
        $this->clientKey = $this->client->client_key;
        $this->secretKey = $this->client->secret_key;
    }

    protected function signedHeaders(array $data = []): array
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $payload, $this->secretKey);

        return [
            'X-Client-Key' => $this->clientKey,
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ];
    }
}
