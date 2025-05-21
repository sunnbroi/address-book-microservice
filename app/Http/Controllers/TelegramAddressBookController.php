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

    // Создание сообщения
    $message = Message::create([
        'id'              => Str::uuid(),
        'address_book_id' => $validated['address_book_id'] ?? null,
        'recipient_id'    => $validated['recipient_id'] ?? null,
        'type'            => $validated['type'],
        'text'            => $validated['text'] ?? null,
        'link'            => $validated['link'] ?? null,
        'sent_at'         => now(),
    ]);

    // Сбор chat_id
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

    if ($chatIds->isEmpty()) {
        Log::warning('Нет получателей для отправки сообщения.', [
            'message_id' => $message->id,
            'address_book_id' => $validated['address_book_id'] ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
        ]);
        return;
    }

    // Батчами по 50 штук с задержкой в секунду
    $chatIds->chunk(50)->each(function ($chunk, $index) use ($message) {
        SendBatchTelegramMessageJob::dispatch(
            $chunk->toArray(),
            $message->id
        )->delay(now()->addSeconds($index));
    });
}
}