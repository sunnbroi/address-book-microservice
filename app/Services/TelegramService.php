<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\AddressBook;
use Illuminate\Support\Facades\Log;


class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token'); // .env
            $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        }


        public function sendMessage(string $chatId, string $text): array
    {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
            Log::info('Telegram response', ['response' => $response]);

            return $this->handleResponse($response);
    }
     
    public function sendMedia(string $type, string $chatId, string $file, ?string $caption = null): array
    {
        $supportedTypes = [
            'photo' => 'sendPhoto',
            'document' => 'sendDocument',
            'video' => 'sendVideo',
            'audio' => 'sendAudio',
            'voice' => 'sendVoice',
        ];

        if (!isset($supportedTypes[$type])) {
            throw new \InvalidArgumentException("Unsupported media type: $type");
        }

        $endpoint = $supportedTypes[$type];
       try {
        
        if (filter_var($file, FILTER_VALIDATE_URL)) {

            $tmpDir = storage_path('app/tmp');

            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true); // рекурсивно создаёт папки
            }
            
            $tmpPath = $tmpDir . '/' . Str::uuid() . '.mp4';

            $videoResponse = Http::timeout(10)->get($file);
            if (!$videoResponse->successful()) {
                throw new \Exception("Failed to download file from URL");
            }

            file_put_contents($tmpPath, $videoResponse->body());

            $filePath = $tmpPath;
        } else {
            // Локальный путь
            $filePath = file_exists($file) ? $file : storage_path("app/public/{$file}");
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
        }

        // Отправляем как multipart
        $response = Http::attach(
            $type,
            fopen($filePath, 'r'),
            basename($filePath)
        )->post("{$this->apiUrl}/{$endpoint}", [
            'chat_id' => $chatId,
            'caption' => $caption ?? '',
        ]);

        return $this->handleResponse($response);
    } catch (\Exception $e) {
        throw $e;
    }
}

    protected function handleResponse($response): array
    {
        $data = $response->json();

        if (!$response->successful()) {
            throw new \Exception("Telegram API error: " . json_encode($data));
        }

        return $data;
    }

    public function isValidChatId(string $chatId): bool
    {
        try {
            $response = Http::get("{$this->apiUrl}/getChat", [
                'chat_id' => $chatId,
            ]);

            $data = $response->json();

            return isset($data['ok']) && $data['ok'] === true;
        } catch (\Exception) {
            return false;
        }
    }

}
