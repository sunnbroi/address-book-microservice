<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendBatchTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $chatIds;
    protected string $messageId;

    public function __construct(array $chatIds, string $messageId)
    {
        $this->chatIds = $chatIds;
        $this->messageId = $messageId;
    }

    public function handle(TelegramService $telegramService): void
    {

        $message = Message::findOrFail($this->messageId);

        $recipients = Recipient::whereIn('chat_id', $this->chatIds)
            ->get()
            ->mapWithKeys(fn ($r) => [(string) $r->chat_id => $r]);

        
foreach ($this->chatIds as $chatIdRaw) {
    $chatId = trim((string) $chatIdRaw);

    $chatId = (int) $chatId;
    $recipient = $recipients->get($chatId);

    $executed = RateLimiter::attempt(
        'telegram-rate-limit',
        $perMinute = 50,
        function () use ($telegramService, $recipient, $message, $chatId) {
            try {

                $response = $telegramService->sendMessage(
                    (string) $recipient->chat_id,
                    (string) $message->text
                );

                DeliveryLog::create([
                    'message_id'      => $message->id,
                    'recipient_id'    => $recipient->id,
                    'address_book_id' => $message->address_book_id,
                    'status'          => 'sent',
                    'sent_at'         => now(),
                ]);
            } catch (\Throwable $e) {
            }
        }
    );

}
    }
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDay();
    }

    public function backoff(): array
    {
        return [60, 300, 1800, 3600, 21600, 43200, 86400];
    }
}
