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
use App\Http\Requests\Message\MessageRequest;
use App\Services\MessageDispatchService;

class TelegramAddressBookController extends Controller
{
    protected MessageDispatchService $dispatchService;

    public function __construct(MessageDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    public function sendMessage(MessageRequest $request): JsonResponse
{
    return $this->dispatchService->dispatch($request->validated());
}

}