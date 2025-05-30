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
            Log::warning("❌ Получатель или сообщение не найдено", [
                'chat_id' => $this->chatId,
                'message_id' => $this->messageId,
            ]);
            return;
        }

        try {
            Log::info("📨 Отправка сообщения Telegram", [
                'chat_id' => $this->chatId,
                'recipient_id' => $recipient->id,
                'message' => $message->only(['id', 'type', 'text']),
            ]);

            $response = $telegramService->sendMessage($this->chatId,$message->text);

            Log::info("📬 Ответ Telegram", [
                'chat_id' => $this->chatId,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Ошибка отправки для chat_id: {$this->chatId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
