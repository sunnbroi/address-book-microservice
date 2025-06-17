<?php

namespace Tests\Feature;

use App\Models\AddressBook;
use App\Models\Client;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TelegramWebhookControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Глобальная установка client_key для тестов с группами
        Client::factory()->create([
            'client_key' => 'default-client-key',
        ]);
        config()->set('services.telegram.default_client_key', 'default-client-key');
    }

    public function test_blocked_user_is_marked_as_inactive(): void
    {
        $recipient = Recipient::factory()->create([
            'chat_id' => '123456789',
            'is_active' => true,
            'blocked_at' => null,
            'updated_at' => now()->subMinutes(10),
        ]);

        $timestamp = now()->subMinute()->timestamp;

        $payload = [
            'my_chat_member' => [
                'chat' => [
                    'id' => '123456789',
                    'type' => 'private',
                ],
                'new_chat_member' => [
                    'status' => 'kicked',
                ],
                'date' => $timestamp,
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        // Получаем актуальную модель из базы, чтобы видеть изменения из другой транзакции
        $recipient = Recipient::find($recipient->id);

        $this->assertFalse($recipient->is_active);
        $this->assertNotNull($recipient->blocked_at);
    }

    public function test_member_user_is_marked_as_active(): void
    {
        $recipient = Recipient::factory()->create([
            'chat_id' => '123456789',
            'is_active' => false,
            'blocked_at' => now()->subDays(1),
            'updated_at' => now()->subMinutes(10),
        ]);

        $timestamp = now()->timestamp;

        $payload = [
            'my_chat_member' => [
                'chat' => [
                    'id' => '123456789',
                    'type' => 'private',
                ],
                'new_chat_member' => [
                    'status' => 'member',
                ],
                'date' => $timestamp,
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        $recipient = Recipient::find($recipient->id);

        $this->assertTrue($recipient->is_active);
        $this->assertNull($recipient->blocked_at);
    }

    public function test_can_handle_start_command_creates_recipient(): void
    {
        $chatId = '987654321';

        $payload = [
            'message' => [
                'message_id' => 1,
                'text' => '/start',
                'chat' => [
                    'id' => $chatId,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser',
                    'type' => 'private',
                ],
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('recipients', [
            'chat_id' => $chatId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'is_active' => true,
        ]);
    }

    public function test_group_chat_migration_updates_address_book_chat_id(): void
    {
        $oldChatId = '-1001234567890';
        $newChatId = '-1009876543210';

        $addressBook = AddressBook::factory()->create([
            'chat_id' => $oldChatId,
        ]);

        $payload = [
            'message' => [
                'chat' => [
                    'id' => $oldChatId,
                    'type' => 'group',
                    'title' => 'Old Group',
                ],
                'migrate_to_chat_id' => $newChatId,
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        $addressBook->refresh();

        $this->assertEquals($newChatId, $addressBook->chat_id);
    }

    public function test_registers_group_if_not_exists(): void
    {
        $chatId = '-1000011223344';

        $payload = [
            'my_chat_member' => [
                'chat' => [
                    'id' => $chatId,
                    'type' => 'supergroup',
                    'title' => 'New Group Chat',
                ],
                'new_chat_member' => [
                    'status' => 'member',
                ],
                'date' => now()->timestamp,
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('address_books', [
            'chat_id' => $chatId,
            'name' => 'New Group Chat',
            'client_key' => 'default-client-key',
        ]);
    }
}
