<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\CreatesClient;

class VerifyHmacSignatureTest extends TestCase
{
    use CreatesClient, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestClient();

        Route::post('/protected-endpoint', function () {
            return response()->json(['message' => 'OK']);
        })->middleware('verify.hmac');

    }

    public function test_missing_headers_returns_401()
    {
        $this->postJson('/protected-endpoint', [])->assertStatus(401)
            ->assertJson(['message' => 'Missing headers: X-Client-Key and X-Signature']);
    }

    public function test_invalid_client_key_returns_401()
    {
        $this->postJson('/protected-endpoint', [], [
            'X-Client-Key' => 'wrong-key',
            'X-Signature' => 'fake',
        ])->assertStatus(401)
            ->assertJson(['message' => 'Invalid client key']);
    }

    public function test_invalid_signature_returns_401()
    {

        $this->postJson('/protected-endpoint', ['test' => 'data'], [
            'X-Client-Key' => $this->clientKey,
            'X-Signature' => 'invalid-signature',
        ])->assertStatus(401)
            ->assertJson(['message' => 'Invalid signature']);
    }

    public function test_valid_signature_allows_request()
    {
        $payload = ['test' => 'valid'];
        $json = json_encode($payload);

        $signature = hash_hmac('sha256', $json, $this->secretKey);

        $this->postJson('/protected-endpoint', $payload, [
            'X-Client-Key' => $this->clientKey,
            'X-Signature' => $signature,
        ])->assertOk()
            ->assertJson(['message' => 'OK']);
    }
}
