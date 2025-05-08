<?php

namespace App\Http\Controllers;
use App\Services\TelegramService;
use App\Models\AddressBook;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AddressBook\MessageAddressBookRequest;
use Illuminate\Http\Request;

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
    
        $results = $this->messageService->sendByType(
            $validated['address_book_id'],
            $validated['type'],
            $validated['message'] ?? null,
            $validated['file'] ?? null
        );
    
        return response()->json(['results' => $results]);
    }
}