<?php
namespace App\Jobs;

use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 7; // максимум 7 попыток (1 + 6 ретраев)

    protected string $chatId;
    protected string $type;
    protected ?string $message;
    protected ?string $file;

    public function __construct(string $chatId, string $type, ?string $message, ?string $file)
    {
        $this->chatId = $chatId;
        $this->type = $type;
        $this->message = $message;
        $this->file = $file;
    }

    public function handle(TelegramService $telegramService)
    {
        RateLimiter::attempt(
            'telegram-send-limit',
            50, // max 50 сообщений
            function () use ($telegramService) {
                if ($this->type === 'message') {
                    $telegramService->sendMessage($this->chatId, $this->message);
                } elseif (in_array($this->type, ['photo', 'document'])) {
                    $telegramService->sendMedia($this->type, $this->chatId, $this->file, $this->message);
                }

                DeliveryLog::create([
                    'chat_id' => $this->chatId,
                    'type' => $this->type,
                    'status' => 'success',
                    'attempts' => $this->attempts(),
                ]);
            },
            1 // секунда
        );
    }

    public function failed(Exception $exception)
    {
        DeliveryLog::create([
            'chat_id' => $this->chatId,
            'type' => $this->type,
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
