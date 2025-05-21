<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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

        foreach ($this->chatIds as $chatId) {
            $recipient = Recipient::where('chat_id', $chatId)->first();

            if (!$recipient) {
                Log::warning("Recipient not found", ['chat_id' => $chatId]);
                continue;
            }

            $alreadySent = DeliveryLog::where([
                ['message_id', $message->id],
                ['recipient_id', $recipient->id],
                ['status', 'success'],
            ])->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                RateLimiter::attempt(
                    'telegram:rate-limit:' . $chatId,
                    50,
                    fn () => $this->sendToRecipient($telegramService, $message, $recipient)
                , 1);
            } catch (\Throwable $e) {
                $this->logFailure($message, $recipient, $chatId, $e->getMessage());
            }
        }
    }

    private function sendToRecipient(TelegramService $telegramService, Message $message, Recipient $recipient): void
    {

        $chatId = $recipient->chat_id;
Log::debug('Отправка начата', [
    'chat_id' => $chatId,
    'type' => $message->type,
    'link' => $message->link,
]);
        $response = match (true) {
            $message->type === 'message' =>
                $telegramService->sendMessage($chatId, $message->text),

            $this->isMediaType($message->type) =>
                $telegramService->sendMedia($message->type, $chatId, $message->link, $message->text),

            default => throw new \InvalidArgumentException("Unsupported message type: {$message->type}")
        };

        $success = is_array($response) && ($response['ok'] ?? false);

        DeliveryLog::updateOrCreate(
            [
                'message_id'   => $message->id,
                'recipient_id' => $recipient->id,
            ],
            [
                'address_book_id' => $message->address_book_id,
                'status'          => $success ? 'success' : 'failed',
                'error'           => $success ? null : json_encode($response, JSON_UNESCAPED_UNICODE),
                'attempts'        => $this->attempts(),
                'delivered_at'    => $success ? now()->format('Y-m-d H:i:s.u') : null,
            ]
        );

        if ($success) {
            Log::info('Сообщение отправлено', ['chat_id' => $chatId]);
        }

        if (!$success) {
            if ($this->attempts() >= 7) {
                DeliveryLog::where('message_id', $message->id)
                    ->where('recipient_id', $recipient->id)
                    ->update(['status' => 'error']);
            }

            throw new \Exception('Telegram API responded with error');
        }
    }

    private function logFailure(Message $message, ?Recipient $recipient, string $chatId, string $error): void
    {
        DeliveryLog::updateOrCreate(
            [
                'message_id' => $message->id,
                'recipient_id' => $recipient?->id,
            ],
            [
                'address_book_id' => $message->address_book_id ?? null,
                'status' => $this->attempts() >= 7 ? 'error' : 'failed',
                'error' => $error,
                'attempts' => $this->attempts(),
            ]
        );

        Log::error('Ошибка при отправке сообщения', [
            'chat_id' => $chatId,
            'error' => $error,
        ]);
    }

    private function isMediaType(string $type): bool
    {
        return in_array($type, ['photo', 'document', 'video', 'audio', 'voice']);
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDays(1);
    }

    public function backoff(): array
    {
        return [60, 300, 1800, 3600, 21600, 43200, 86400];
    }
}
