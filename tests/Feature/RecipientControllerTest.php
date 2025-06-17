<?php

namespace Tests\Feature;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\CreatesClient;

class RecipientControllerTest extends TestCase
{
    use CreatesClient, RefreshDatabase;

    public function test_can_create_recipient(): void
    {
        $this->createTestClient();

        $addressBook = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
        ]);

        $payload = [
            'chat_id' => Str::uuid()->toString(),
            'username' => 'test_user',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'type' => 'user',
        ];

        $response = $this->postJson("/api/address-books/{$addressBook->id}", $payload, $this->signedHeaders($payload));

        $response->assertCreated()
            ->assertJsonFragment([
                'chat_id' => $payload['chat_id'],
                'username' => $payload['username'],
            ]);
    }

    public function test_can_update_recipient(): void
    {
        $this->createTestClient();

        $recipient = Recipient::factory()->create();

        $newChatId = Str::uuid()->toString();

        $response = $this->putJson("/api/recipients/{$recipient->id}", [
            'chat_id' => $newChatId,
        ], $this->signedHeaders([
            'chat_id' => $newChatId,
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'chat_id' => $newChatId,
            ]);

        $this->assertDatabaseHas('recipients', [
            'id' => $recipient->id,
            'chat_id' => $newChatId,
        ]);
    }

    public function test_can_show_recipients_by_address_book(): void
    {
        $this->createTestClient();

        $addressBook = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
        ]);

        $payload1 = [
            'chat_id' => Str::uuid()->toString(),
            'username' => 'user1',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'type' => 'user',
        ];
        $payload2 = [
            'chat_id' => Str::uuid()->toString(),
            'username' => 'user2',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'type' => 'user',
        ];

        $this->postJson("/api/address-books/{$addressBook->id}", $payload1, $this->signedHeaders($payload1));
        $this->postJson("/api/address-books/{$addressBook->id}", $payload2, $this->signedHeaders($payload2));

        $response = $this->getJson("/api/recipients/{$addressBook->id}", $this->signedHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'recipients' => [
                    '*' => ['id', 'chat_id', 'username', 'first_name', 'last_name', 'type'],
                ],
            ])
            ->assertJsonFragment(['username' => 'user1'])
            ->assertJsonFragment(['username' => 'user2']);
    }

    public function test_can_delete_recipient_from_address_book(): void
    {
        $this->createTestClient();

        $addressBook = AddressBook::factory()->create([
            'client_key' => $this->clientKey,
        ]);

        $recipient = Recipient::factory()->create();
        $addressBook->recipients()->attach($recipient->id);

        $response = $this->deleteJson("/api/address-books/{$addressBook->id}/{$recipient->id}", [], $this->signedHeaders());

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Recipient detached and deleted']);

        $this->assertSoftDeleted('recipients', ['id' => $recipient->id]);
        $this->assertDatabaseMissing('address_books_recipients', [
            'address_book_id' => $addressBook->id,
            'recipient_id' => $recipient->id,
        ]);
    }
}
