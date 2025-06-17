<?php

namespace Tests\Feature;

use App\Models\AddressBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesClient;

class AddressBookControllerTest extends TestCase
{
    use CreatesClient, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestClient();
    }

    protected function signedHeaders(array $payload = []): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return [
            'X-Client-Key' => $this->clientKey,
            'X-Signature' => hash_hmac('sha256', $json, $this->secretKey),
            'Content-Type' => 'application/json',
        ];
    }

    public function test_index_returns_books()
    {
        AddressBook::factory()->count(2)->create(['client_key' => $this->clientKey]);

        $headers = $this->signedHeaders();

        $response = $this->getJson('/api/address-books', $headers);
        $response->assertOk()->assertJsonCount(2);
    }

    public function test_store_creates_new_book()
    {
        $payload = ['name' => 'Test Book'];
        $headers = $this->signedHeaders($payload);

        $response = $this->postJson('/api/address-books', $payload, $headers);
        $response->assertCreated()->assertJsonFragment(['name' => 'Test Book']);
    }

    public function test_store_restores_deleted_book()
    {
        $book = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
            'name' => 'Old Book',
            'deleted_at' => now(),
        ]);

        $payload = [
            'name' => 'Old Book',
            'address_book_id' => $book->id,
        ];
        $headers = $this->signedHeaders($payload);

        $response = $this->postJson('/api/address-books', $payload, $headers);
        $response->assertOk()->assertJsonFragment(['message' => 'Address book restored']);
    }

    public function test_show_returns_book()
    {
        $book = AddressBook::factory()->create(['client_key' => $this->clientKey]);

        $headers = $this->signedHeaders();

        $response = $this->getJson("/api/address-books/{$book->id}", $headers);
        $response->assertOk()->assertJsonFragment(['id' => $book->id]);
    }

    public function test_update_changes_book()
    {
        $book = AddressBook::factory()->create(['client_key' => $this->clientKey]);

        $payload = ['name' => 'Updated Book'];
        $headers = $this->signedHeaders($payload);

        $response = $this->putJson("/api/address-books/{$book->id}", $payload, $headers);
        $response->assertOk()->assertJsonFragment(['name' => 'Updated Book']);
    }

    public function test_destroy_deletes_book()
    {
        $book = AddressBook::factory()->create(['client_key' => $this->clientKey]);
        $headers = $this->signedHeaders();

        $response = $this->deleteJson("/api/address-books/{$book->id}", [], $headers);
        $response->assertOk()->assertJson(['message' => 'Address book deleted']);
        $this->assertSoftDeleted(AddressBook::class, ['id' => $book->id]);
    }
}
