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

class TelegramAddressBookController extends Controller
{
    protected TelegramService $messageService;
    public function __construct(TelegramService $messageService)
    {
        $this->messageService = $messageService;
    }
    public function sendMessage(MessageAddressBookRequest $request): JsonResponse
    {
        $validated = $request->validated();
    
        $message = Message::create([
            'id' => Str::uuid(),
            'address_book_id' => $validated['address_book_id'] ?? null,
            'recipient_id' => $validated['chat_id'] ?? null,
            'type' => $validated['type'],
            'text' => $validated['message'] ?? null,
            'file' => $validated['file'] ?? null,
        ]);
    
        if ($message->recipient_id) {
            SendSingleTelegramMessageJob::dispatch($message->id, $message->recipient_id);
        } elseif ($message->address_book_id) {
            SendTelegramToAddressBookJob::dispatch($message->id);
        }
        return response()->json(['status' => 'job dispatched']);
        }

    }