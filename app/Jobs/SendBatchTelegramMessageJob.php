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
            RateLimiter::attempt(
        'telegram:rate-limit',
        50,
        function () use ($telegramService, $chatId, $message) {
            $telegramService->sendMessage($chatId, $message->text);
        },
        1
    );
}
    }
}
