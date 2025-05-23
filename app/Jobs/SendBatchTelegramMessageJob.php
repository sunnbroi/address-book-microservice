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
                Log::info('▶️ Начало обработки батча', [
            'time' => now()->toDateTimeString(),
            'chat_ids' => $this->chatIds,
        ]);
        $message = Message::findOrFail($this->messageId);

        $recipients = Recipient::whereIn('chat_id', $this->chatIds)->get()->keyBy('chat_id');
        $recipientIds = $recipients->pluck('id')->all();

        $sentLogs = DeliveryLog::where('message_id', $message->id)
            ->whereIn('recipient_id', $recipientIds)
            ->where('status', 'success')
            ->pluck('recipient_id')
            ->flip();

        $logBatch = [];
        $successfulLogs = [];
        $now = now();

        foreach ($this->chatIds as $chatId) {
    $recipient = $recipients[$chatId] ?? null;

    if (!$recipient || isset($sentLogs[$recipient->id])) {
        continue;
    }

    try {
        $chatId = $recipient->chat_id;

        $response = match (true) {
            $message->type === 'message' =>
                $telegramService->sendMessage($chatId, $message->text),
            $this->isMediaType($message->type) =>
                $telegramService->sendMedia($message->type, $chatId, $message->link, $message->text),
            default => throw new \InvalidArgumentException("Unsupported message type: {$message->type}"),
        };

        $success = is_array($response) && ($response['ok'] ?? false);

        $logBatch[] = $this->createLogEntry(
            $message,
            $recipient,
            $success ? 'success' : ($this->attempts() >= 7 ? 'error' : 'failed'),
            $success ? null : json_encode($response, JSON_UNESCAPED_UNICODE),
            $success ? $now->format('Y-m-d H:i:s.u') : null,
            $now
        );

        if ($success) {
            $successfulLogs[] = $chatId;
        }
    } catch (\Throwable $e) {
        $this->logFailure($message, $recipient, $chatId, $e->getMessage(), $logBatch, $now);
    }
}
        if (!empty($logBatch)) {
            LogDeliveryResultJob::dispatch($logBatch);
        }

        if (!empty($successfulLogs)) {
            Log::info('Успешно отправленные сообщения', ['chat_ids' => $successfulLogs]);
        }
    }

    private function logFailure(Message $message, ?Recipient $recipient, string $chatId, string $error, array &$logBatch, $now): void
    {
        $logBatch[] = $this->createLogEntry(
            $message,
            $recipient,
            $this->attempts() >= 7 ? 'error' : 'failed',
            $error,
            null,
            $now
        );

        Log::error('Ошибка при отправке сообщения', [
            'chat_id' => $chatId,
            'error' => $error,
        ]);
    }

    private function createLogEntry(
        Message $message,
        Recipient $recipient,
        string $status,
        ?string $error = null,
        ?string $deliveredAt = null,
        $now = null
    ): array {
        return [
            'message_id'      => $message->id,
            'recipient_id'    => $recipient->id,
            'address_book_id' => $message->address_book_id,
            'status'          => $status,
            'error'           => $error,
            'attempts'        => $this->attempts(),
            'delivered_at'    => $deliveredAt,
            'created_at'      => $now ?? now(),
            'updated_at'      => $now ?? now(),
        ];
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
