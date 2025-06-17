<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendSingleTelegramMessageJob;
use App\Models\Message;
use App\Models\Recipient;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendSingleTelegramMessageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_successfully_sends_message(): void
    {
        $this->withoutExceptionHandling();
        $this->refreshDatabase();

        // Arrange: создаём реального получателя и сообщение
        $recipient = Recipient::factory()->create([
            'chat_id' => '987654321',
        ]);

        $message = Message::factory()->create([
            'text' => 'Hello Telegram!',
        ]);

        // Мокаем TelegramService
        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldReceive('sendMessage')
            ->with($recipient->chat_id, $message->text)
            ->once()
            ->andReturn(['ok' => true]);

        $this->app->instance(TelegramService::class, $telegramMock);

        // Act: выполняем джобу
        $job = new SendSingleTelegramMessageJob($recipient->chat_id, $message->id);
        $job->handle($telegramMock);

        // Assert
        $this->assertTrue(true); // если исключений не было, считаем что успешно
    }

    public function test_handle_does_nothing_if_recipient_or_message_is_missing(): void
    {
        $this->refreshDatabase();

        // Только recipient, без message
        $recipient = Recipient::factory()->create([
            'chat_id' => '123456789',
        ]);

        $missingMessageId = 'non-existent-msg-id';

        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldNotReceive('sendMessage');

        $this->app->instance(TelegramService::class, $telegramMock);

        $job = new SendSingleTelegramMessageJob($recipient->chat_id, $missingMessageId);
        $job->handle($telegramMock);

        $this->assertTrue(true); // без падений = успех
    }

    public function test_handle_catches_exception_from_telegram(): void
    {
        $this->refreshDatabase();

        $recipient = Recipient::factory()->create([
            'chat_id' => '555777333',
        ]);

        $message = Message::factory()->create([
            'text' => 'This will fail',
        ]);

        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldReceive('sendMessage')
            ->with($recipient->chat_id, $message->text)
            ->once()
            ->andThrow(new \Exception('Simulated Telegram failure'));

        $this->app->instance(TelegramService::class, $telegramMock);

        // Act
        $job = new SendSingleTelegramMessageJob($recipient->chat_id, $message->id);

        // catch-logic в job проглотит исключение, тест не упадёт
        $job->handle($telegramMock);

        $this->assertTrue(true);
    }
}
