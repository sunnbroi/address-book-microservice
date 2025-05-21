<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;
use App\Jobs\SendBatchTelegramMessageJob;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class SendBatchTelegramMessageJobTest extends TestCase
{
    use RefreshDatabase, MockeryPHPUnitIntegration;

    public function test_it_sends_message_and_creates_delivery_log()
    {
        $message = Message::factory()->create([
            'type' => 'message',
            'text' => 'Hello test',
        ]);

        $recipient = Recipient::factory()->create([
            'chat_id' => '123456789',
        ]);

        $job = new SendBatchTelegramMessageJob([$recipient->chat_id], $message->id);

        // Мокаем TelegramService
        $telegramServiceMock = \Mockery::mock(TelegramService::class);
        $telegramServiceMock
            ->shouldReceive('sendMessage')
            ->with($recipient->chat_id, $message->text)
            ->andReturn(['ok' => true]);

        // Мокаем RateLimiter — чтобы пропускал попытку
        RateLimiter::clear('telegram:rate-limit:' . $recipient->chat_id);

        // Act
        $job->handle($telegramServiceMock);

        // Assert: запись появилась
        $this->assertDatabaseHas('delivery_logs', [
            'message_id' => $message->id,
            'recipient_id' => $recipient->id,
            'status' => 'success',
        ]);
    }

    public function test_it_logs_failure_when_telegram_fails()
    {
        Log::spy(); // Подменим логгер

        $message = Message::factory()->create([
            'type' => 'message',
            'text' => 'Failing message',
        ]);

        $recipient = Recipient::factory()->create([
            'chat_id' => '999999999',
        ]);

        $job = new SendBatchTelegramMessageJob([$recipient->chat_id], $message->id);

        $telegramServiceMock = \Mockery::mock(TelegramService::class);
        $telegramServiceMock
            ->shouldReceive('sendMessage')
            ->andReturn(['ok' => false]);

        RateLimiter::clear('telegram:rate-limit:' . $recipient->chat_id);

        $job->handle($telegramServiceMock);

        $this->assertDatabaseHas('delivery_logs', [
            'message_id' => $message->id,
            'recipient_id' => $recipient->id,
            'status' => 'failed',
        ]);

        Log::shouldHaveReceived('error')->once();
    }
}
