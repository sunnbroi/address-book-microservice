<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use App\Models\DeliveryLog;

class SendTelegramToAddressBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $message_id;
    public $tries = 7;

    public function __construct(string $message_id)
    {
        $this->message_id = $message_id;
    }

    public function handle()
    {
        $message = Message::with('addressBook.recipients')->findOrFail($this->message_id);

        foreach ($message->addressBook->recipients as $recipient) {
            SendSingleTelegramMessageJob::dispatch($message->id, $recipient->id);
        }

        $message->update(['sent_at' => now()]);
    }

    public function failed(Exception $exception)
    {
        $message = Message::find($this->message_id);

        if ($message) {
            DeliveryLog::create([
                'message_id' => $message->id,
                'address_book_id' => $message->address_book_id,
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'attempts' => $this->attempts(),
            ]);
        }
    }

    public function backoff(): array
    {
        return [60, 300, 1800, 3600, 21600, 43200, 86400];
    }
}
