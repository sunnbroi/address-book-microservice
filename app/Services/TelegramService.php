<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function sendMessage(string $chatId, string $text): bool
    {
        
        $response = Http::post(config('services.telegram.api_url') . '/bot' . config('services.telegram.token') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        return $response->ok();
    }
}