<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use Illuminate\Support\Str;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Message $message;
    protected Recipient $recipient;

    public function __construct(Message $message, Recipient $recipient)
    {
        $this->message = $message;
        $this->recipient = $recipient;
    }

    public function handle(): void
    {
        \Log::info("📤 Отправка сообщения через Telegram: {$this->recipient->username}", 
        [
            'message' => $this->message->toArray(),
            'recipient' => $this->recipient->toArray(),
        ]);

        try {
            $status = 'OK';
            $error = null;
        } catch (\Throwable $e) {
            $status = 'ERROR';
            $error = $e->getMessage();
        }

        DeliveryLog::create([
            'message_id' => (string) $this->message->id,
            'recipient_id' => (string) $this->recipient->getKey(), // 👈 здесь главное: UUID
            'address_book_id' => (string) $this->message->address_book_id,
            'status' => $status,
            'error_message' => $error,
        ]);
}
}