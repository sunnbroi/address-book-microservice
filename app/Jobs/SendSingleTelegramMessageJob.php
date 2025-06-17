<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Recipient;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSingleTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;

    protected string $messageId;

    protected string $recipientId;

    public function __construct(string $chatId, string $messageId)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    public function handle(TelegramService $telegramService): void
    {

        $recipient = Recipient::where('chat_id', $this->chatId)->first();
        $this->recipientId = $recipient?->id;
        $message = Message::find($this->messageId);

        if (! $recipient || ! $message) {
            return;
        }

        try {
            $response = $telegramService->sendMessage($this->chatId, $message->text);
        } catch (\Throwable $e) {
        }
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getChatId(): string
    {
        return $this->chatId;
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }
}
