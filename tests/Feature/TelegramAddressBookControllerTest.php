<?php

namespace Tests\Feature;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\CreatesClient;

class TelegramAddressBookControllerTest extends TestCase
{
    use CreatesClient, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestClient();
    }

    public function test_dispatch_successfully_with_address_book()
    {
        Queue::fake();

        $book = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
        ]);

        $data = [
            'address_book_id' => $book->id,
            'type' => 'message',
            'text' => 'Hello',
        ];

        $response = $this->postJson('/api/messages', $data, $this->signedHeaders($data));
        $response->assertOk();
    }

    public function test_dispatch_successfully_with_recipient()
    {
        Queue::fake();

        $recipient = Recipient::factory()->create();
        $book = AddressBook::factory()->create(['client_key' => $this->clientKey]);
        $book->recipients()->attach($recipient);
        $data = [
            'recipient_id' => $recipient->id,
            'address_book_id' => $book->id,
            'type' => 'message',
            'text' => 'Hi',
        ];
        $response = $this->postJson('/api/messages', $data, $this->signedHeaders($data));
        $response->assertOk();
    }

    public function test_validation_fails_without_recipient_and_book()
    {
        $data = [
            'type' => 'message',
            'text' => 'Fails',
        ];

        $response = $this->postJson('/api/messages', $data, $this->signedHeaders($data));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address_book_id']);
    }

    public function test_validation_fails_with_invalid_type()
    {
        $recipient = Recipient::factory()->create();
        $book = AddressBook::factory()->create(['client_key' => $this->clientKey]);
        $book->recipients()->attach($recipient);

        $data = [
            'recipient_id' => $recipient->id,
            'type' => 'invalid_type',
            'text' => 'Test',
        ];

        $response = $this->postJson('/api/messages', $data, $this->signedHeaders($data));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_validation_fails_if_link_required_but_missing()
    {
        $this->createTestClient();

        $book = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
        ]);

        $recipient = Recipient::factory()->create();

        $data = [
            'address_book_id' => $book->id,
            'recipient_id' => $recipient->id,
            'type' => 'photo', // требует link
            'text' => '...',   // обязателен
        ];

        $response = $this->postJson('/api/messages', $data, $this->signedHeaders($data));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['link']);
    }
}
