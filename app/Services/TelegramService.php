<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token'); // .env
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }


    protected function sendRequest(string $method, array $params = []): array
    {
        $response = Http::post($this->apiUrl . $method, $params);

        if ($response->failed()) {
            throw new \Exception('Telegram API request failed: ' . $response->body());
        }

        return $response->json();
    }

    public function sendMessage(string $chatId, string $text): array
    {
        return $this->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function sendPhoto(string $chatId, $photo, string $caption = null): array
    {
        if (is_string($photo)) {
            // Отправляем ссылку
            $response = Http::post($this->apiUrl . 'sendPhoto', [
                'chat_id' => $chatId,
                'photo' => $photo,
                'caption' => $caption ?? '',
            ]);
        } else {
            // Отправляем файл
            $response = Http::asMultipart()
                ->attach(
                    'photo',
                    fopen($photo->getRealPath(), 'r'),
                    $photo->getClientOriginalName()
                )
                ->post($this->apiUrl . 'sendPhoto', [
                    ['name' => 'chat_id', 'contents' => $chatId],
                    ['name' => 'caption', 'contents' => $caption ?? ''],
                ]);
        }

        if ($response->failed()) {
            throw new \Exception('Telegram API request failed: ' . $response->body());
        }

        return $response->json();
}

public function sendDocument(string $chatId, $document, string $caption = null): array
    {
        if (is_string($document)) {
            // Отправляем ссылку
            $response = Http::post($this->apiUrl . 'sendDocument', [
                'chat_id' => $chatId,
                'document' => $document,
                'caption' => $caption ?? '',
            ]);
        } else {
            // Отправляем файл
            $response = Http::asMultipart()
                ->attach(
                    'document',
                    fopen($document->getRealPath(), 'r'),
                    $document->getClientOriginalName()
                )
                ->post($this->apiUrl . 'sendDocument', [
                    ['name' => 'chat_id', 'contents' => $chatId],
                    ['name' => 'caption', 'contents' => $caption ?? ''],
                ]);
        }

        if ($response->failed()) {
            throw new \Exception('Telegram API request failed: ' . $response->body());
        }

        return $response->json();
}
}