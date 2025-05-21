<?php
namespace App\Jobs;
use App\Models\Message;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\DeliveryLog;
use App\Models\Recipient;


use Illuminate\Support\Facades\Log;

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
            $recipient = Recipient  ::where('chat_id', $chatId)->first();

            $existingLog = DeliveryLog::where('message_id', $message->id)
                ->where('recipient_id', $recipient->id)
                ->where('status', 'success')
                ->first();   


            if ($existingLog) {
                continue; // уже отправлено, пропускаем
            }
            RateLimiter::attempt(
                'telegram:rate-limit',
                50,
                function () use ($telegramService, $chatId, $message, $recipient) {
                try {
                    if ($message->type === 'message') {
                    $response = $telegramService->sendMessage($chatId, $message->text);
                    } elseif (in_array($message->type, ['photo', 'document', 'video', 'audio', 'voice'])) {
                        $response = $telegramService->sendMedia(
                                $message->type,
                                $chatId,
                                $message->link,
                                $message->text
                            );
                    }
                    
                        $success = $response['ok'] ?? false;


                        DeliveryLog::updateOrCreate(
                        [
                            'message_id'   => $message->id,
                            'recipient_id' => $recipient->id,
                        ],
                        [
                            'address_book_id' => $message->address_book_id,
                            'status'          => $success ? 'success' : 'failed',
                            'error'           => $success ? null : json_encode($response),
                            'attempts'        => $this->attempts(),
                            'delivered_at'    => $success ? now() : null,
                        ]
                    );

                    if (!$success && $this->attempts() >= 7) {
                        DeliveryLog::where('message_id', $message->id)
                            ->where('recipient_id', $recipient->id)
                            ->update([
                                'status' => 'error',
                                'error'  => json_encode($response),
                            ]);
                    }
                    if (!$success) {
                        throw new \Exception('Telegram API responded with error');
                    }

                } catch (\Exception $e) {
                    DeliveryLog::updateOrCreate(
                        [
                            'message_id'   => $message->id,
                            'recipient_id' => $recipient?->id,
                        ],
                        [
                            'address_book_id' => $message->address_book_id ?? null,
                            'status'          => $this->attempts() >= 7 ? 'error' : 'failed',
                            'error'           => $e->getMessage(),
                            'attempts'        => $this->attempts(),
                        ]
                    );
                    if ($this->attempts() < 7) {
                        throw $e;
                    }
                }
            },
            1
        );
    }
    }
        public function retryUntil()
    {
        return now()->addDays(1);
    }

        public function backoff(): array
    {
        return [60, 300, 1800, 3600, 21600, 43200, 86400]; // секунды: 1м, 5м, 30м, 1ч, 6ч, 12ч, 24ч
    }
}
