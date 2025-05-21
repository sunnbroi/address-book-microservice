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
        
        $message = Message::create([
            'id' => Str::uuid(),
            'address_book_id' => $validated['address_book_id'] ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'type' => $validated['type'],
            'text' => $validated['text'] ?? null,
            'link' => $validated['link'] ?? null,
        ]);

         $chatIds = collect();

    if (!empty($validated['address_book_id'])) {
        $addressBook = AddressBook::with('recipients')->findOrFail($validated['address_book_id']);
        $chatIds = $chatIds->merge($addressBook->recipients->pluck('chat_id'));
    }

    if (!empty($validated['recipient_id'])) {
        $recipient = Recipient::findOrFail($validated['recipient_id']);
        $chatIds->push($recipient->chat_id);
    }

    $chatIds = $chatIds->unique()->filter();

         $chunks = $chatIds->chunk(50);

        foreach ($chunks as $index => $chunk) {
        SendBatchTelegramMessageJob::dispatch(
            $chunk->toArray(),
            $message->id
        )->delay(now()->addSeconds($index)); // задержка между батчами
    }
}
}
