<?php

namespace App\Http\Controllers;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramPersonalMessageController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }
    public function sendMessage(Request $request): JsonResponse
    {
        $chatId = $request->input('chat_id');
        $text = $request->input('text');
        $caption = $request->input('caption');
        $photo = $request->file('photo');
        $document = $request->file('document');

        $results = [];

        if ($text && !$photo && !$document) {
            
            $results['message'] = $this->telegram->sendMessage($chatId, $text);
        }
    
        if ($photo) {
            $results['photo'] = $this->telegram->sendPhoto($chatId, $photo, $caption);
        }
    
        if ($document) {
            $results['document'] = $this->telegram->sendDocument($chatId, $document, $caption);
        }
    
        return response()->json($results);
    }
    }
