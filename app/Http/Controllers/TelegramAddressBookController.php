<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\MessageRequest;
use App\Services\MessageDispatchService;
use Illuminate\Http\JsonResponse;

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
