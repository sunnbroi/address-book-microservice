<?php
namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreMessageRequest;
use App\DTO\Message\StoreMessageDTO;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $service
    ) {}

    public function store(StoreMessageRequest $request): JsonResponse
    {
        \Log::info('🎯 Контроллер вызван');
    
        $dto = StoreMessageDTO::fromRequest($request);
    
        $message = $this->service->createAndDispatch($dto);
    
        return response()->json($message);
    }

}
