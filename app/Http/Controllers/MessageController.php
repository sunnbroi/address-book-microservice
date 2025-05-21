<?php
namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\Message;
use App\Models\Recipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\SendSingleTelegramMessageJob;
use App\Http\Requests\Message\MessageSendRequest;

class MessageController extends Controller
{
    protected $clientKey;
    public function __construct() { 
        $this->clientKey = request()->header('X-Client-Key');
    }
    public function store(MessageSendRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $addressBook = AddressBook::where('client_key', $this->clientKey)
            ->where('id', $validated['address_book_id'])
            ->first();
        
        if (!$addressBook) {
            return response()->json(['message' => 'Address book not found'], 404);
        }

        $messageData  = [
            'id' => Str::uuid(),
            'address_book_id' => $validated['address_book_id'],
            'text' => $validated['text'],
            'type' => $validated['type'],
            'link' => $validated['link'] ?? null,
        ];

        $message = Message::create($messageData);

        foreach ($addressBook->recipients as $recipient) {
            SendSingleTelegramMessageJob::dispatch($message->id, $recipient->id, $message->type)->onQueue('telegram');
        }

        return response()->json(['message_id' => $message->id]);
    }
}
