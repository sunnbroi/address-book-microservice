<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class SendSingleTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 7;

    protected string $message_id;
    protected string $recipient_id;

    public function __construct(string $message_id, string $recipient_id)
    {
        $this->message_id = $message_id;
        $this->recipient_id = $recipient_id;
    }

    public function handle(TelegramService $telegramService)
    {
        $message = Message::findOrFail($this->message_id);
        $recipient = Recipient::findOrFail($this->recipient_id);
        // Ограничение 50 сообщений в секунду
        RateLimiter::attempt(
            'telegram-send-limit:global',
            50,
            function () use ($telegramService, $message, $recipient) {
                try {
                    if ($message->type === 'message') {
                        $telegramService->sendMessage($recipient->telegram_chat_id, $message->text);
                    } elseif (in_array($message->type, ['photo', 'document'])) {
                        $telegramService->sendMedia($message->type, $recipient->telegram_chat_id, $message->file, $message->text);
                    }

                    DeliveryLog::create([
                        'message_id' => $message->id,
                        'address_book_id' => $message->address_book_id,
                        'recipient_id' => $recipient->id,
                        'status' => 'success',
                        'attempts' => $this->attempts(),
                        'delivered_at' => now(),
                    ]);
                } catch (Exception $e) {
                    // Логируем индивидуальную ошибку
                    DeliveryLog::create([
                        'message_id' => $message->id,
                        'address_book_id' => $message->address_book_id,
                        'recipient_id' => $recipient->id,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'attempts' => $this->attempts(),
                    ]);

                    // Пробрасываем ошибку, чтобы сработал retry и backoff
                    throw $e;
                }
            },
            1 // секунда
        );
    }

    public function failed(Exception $exception)
    {
        // Важно: логируем сбой всего job (например, если не сработал RateLimiter или всё упало)
        DeliveryLog::create([
            'message_id' => $this->message_id,
            'address_book_id' => Message::find($this->message_id)?->address_book_id,
            'recipient_id' => $this->recipient_id,
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    public function backoff(): array
    {
        return [60, 300, 1800, 3600, 21600, 43200, 86400]; // 1м, 5м, 30м, 1ч, 6ч, 12ч, 24ч
    }
}
