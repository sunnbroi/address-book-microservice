<?php

namespace App\Services;

use App\Models\Message;
use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Jobs\SendBatchTelegramMessageJob;   
use App\Jobs\SendSingleTelegramMessageJob;

class MessageDispatchService
{
    public function dispatch(array $validated): JsonResponse
    {
        Log::info('📩 Контроллер вызван');

        try {
            $message = $this->createMessage($validated);
        } catch (\Throwable $e) {
            Log::error('❌ Ошибка при создании сообщения', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Ошибка при создании сообщения'], 500);
        }

        $chatIds = $this->getChatIds($validated);

        if ($chatIds->isEmpty()) {
            Log::warning('⚠️ Нет получателей для отправки сообщения.', [
                'message_id' => $message->id,
            ]);
            return response()->json(['message' => 'Нет получателей для отправки'], 400);
        }

        $this->dispatchJobs($chatIds, $message->id);

        return response()->json([
            'message_id' => $message->id,
            'recipients_total' => $chatIds->count(),
            'batches' => ceil($chatIds->count() / 50),
            'status' => 'queued',
        ]);
    }

    private function createMessage(array $validated): Message
    {
        return Message::create([
            'id'              => (string) Str::uuid(),
            'address_book_id' => $validated['address_book_id'] ?? null,
            'recipient_id'    => $validated['recipient_id'] ?? null,
            'type'            => $validated['type'],
            'text'            => $validated['text'] ?? null,
            'link'            => $validated['link'] ?? null,
            'sent_at'         => now(),
        ]);
    }

    private function getChatIds(array $validated): Collection
    {
        $chatIds = collect();

        if (!empty($validated['address_book_id'])) {
            $addressBook = AddressBook::with('recipients')->findOrFail($validated['address_book_id']);
            $chatIds = $chatIds->merge($addressBook->recipients->pluck('chat_id'));
        }

        if (!empty($validated['recipient_id'])) {
            $recipient = Recipient::findOrFail($validated['recipient_id']);
            $chatIds->push($recipient->chat_id);
        }

        return $chatIds->filter()->unique()->values();
    }

   private function dispatchJobs(Collection $chatIds, string $messageId): void
{
    foreach ($chatIds->values() as $index => $chatId) {
        $delay = now()->addMilliseconds($index * 20); // 50 сообщений / сек
        SendSingleTelegramMessageJob::dispatch((string) $chatId, $messageId)->delay($delay); 
    }
}
}
