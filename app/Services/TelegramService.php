<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AddressBook;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token'); // .env
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }


    public function sendMessage(string $chatId, string $text): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Telegram sendMessage failed', ['response' => $data]);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function sendMedia(string $type, string $chatId, string $file, ?string $caption = null): array
    {
        $endpoint = $type === 'photo' ? 'sendPhoto' : 'sendDocument';

        try {
            $response = Http::attach(
                $type, // поле multipart (например, photo или document)
                fopen($file, 'r'),
                basename($file)
            )->post("{$this->apiUrl}/{$endpoint}", [
                'chat_id' => $chatId,
                'caption' => $caption,
            ]);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error("Telegram {$endpoint} failed", ['response' => $data]);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Telegram {$endpoint} exception", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}



    /*protected function sendRequest(string $method, array $params = []): array
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

    public function sendMessageToAddressBook(string $addressBookId, string $text): array
    {
        $addressBook = AddressBook::with('recipients')->findOrFail($addressBookId);

        $results = [];

        foreach ($addressBook->recipients as $recipient) {
            $chatId = $recipient->telegram_user_id;
            $result = $this->sendMessage($chatId, $text);

            $results[] = [
                'recipient_id' => $recipient->id,
                'chat_id' => $chatId,
                'status' => $result['ok'] ?? false,
                'response' => $result,
            ];
        }

        return $results;
    }
    public function sendMedia(string $type, string $chatId, $media, ?string $caption = null): array
    {
        $method = $type === 'photo' ? 'sendPhoto' : 'sendDocument';
    
        if (is_string($media)) {
            $response = Http::post($this->apiUrl . $method, [
                'chat_id' => $chatId,
                $type => $media,
                'caption' => $caption ?? '',
            ]);
        } else {
            $response = Http::asMultipart()
                ->attach(
                    $type,
                    fopen($media->getRealPath(), 'r'),
                    $media->getClientOriginalName()
                )
                ->post($this->apiUrl . $method, [
                    ['name' => 'chat_id', 'contents' => $chatId],
                    ['name' => 'caption', 'contents' => $caption ?? ''],
                ]);
        }
    
        if ($response->failed()) {
            throw new \Exception('Telegram API request failed: ' . $response->body());
        }
    
        return $response->json();
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

public function sendByType(string $addressBookId, string $type, ?string $message, ?string $file): array
    {
        $addressBook = AddressBook::with('recipients')->findOrFail($addressBookId);

        $results = [];

        foreach ($addressBook->recipients as $recipient) {
            $chatId = $recipient->telegram_user_id;

            if ($type === 'message') {
                $result = $this->sendMessage($chatId, $message);
            } elseif ($type === 'photo') {
                $result = $this->sendPhoto($chatId, $file, $message); // передаём message как caption
            } elseif ($type === 'document') {
                $result = $this->sendDocument($chatId, $file, $message); // передаём message как caption
            } else {
                continue; // skip unknown type
            }

            $results[] = [
                'recipient_id' => $recipient->id,
                'chat_id' => $chatId,
                'status' => $result['ok'] ?? false,
                'response' => $result,
            ];
        }

        return $results;
    }
*/
