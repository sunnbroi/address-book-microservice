<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendSingleTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;
    protected string $messageId;

    public function __construct(string $chatId, string $messageId)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    public function handle(TelegramService $telegramService): void
    {

        $recipient = Recipient::where('chat_id', $this->chatId)->first();
        $message = Message::find($this->messageId);

        if (!$recipient || !$message) {
            return;
        }

        try {
            $response = $telegramService->sendMessage($this->chatId,$message->text);
        } catch (\Throwable $e) {
        }
    }
}
