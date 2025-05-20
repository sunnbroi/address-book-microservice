<?php

namespace App\Http\Controllers;
use App\Services\TelegramService;
use App\Models\AddressBook;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AddressBook\MessageAddressBookRequest;
use Illuminate\Http\Request;
use App\Jobs\SendTelegramToAddressBookJob;
use App\Jobs\SendSingleTelegramMessageJob;
use App\Models\Message;
use Illuminate\Support\Str;
use App\Models\Recipient;
use App\Models\DeliveryLog;
use App\Jobs\SendBatchTelegramMessageJob;
use Illuminate\Support\Facades\Log;

class TelegramAddressBookController extends Controller
{
    protected TelegramService $messageService;
    public function __construct(TelegramService $messageService)
    {
        $this->messageService = $messageService;
    }
    public function sendMessage(MessageAddressBookRequest $request): void
    {
        $validated = $request->validated();
        $addressBookId = $validated['address_book_id'];
        $message = Message::create([
            'id' => Str::uuid(),
            'address_book_id' => $addressBookId ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'type' => $validated['type'],
            'text' => $validated['text'] ?? null,
            'file' => $validated['file'] ?? null,
        ]);

        $chatIds = AddressBook::findOrFail($addressBookId)
            ->recipients()
            ->pluck('chat_id');
         $chunks = $chatIds->chunk(50);

        foreach ($chunks as $index => $chunk) {
        SendBatchTelegramMessageJob::dispatch(
            $chunk->toArray(),
            $message->id
        )->delay(now()->addSeconds($index)); // задержка между батчами
    }
}
}
