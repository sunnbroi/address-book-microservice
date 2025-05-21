<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Recipient;
use App\Models\AddressBook;
use Illuminate\Support\Carbon;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): \Illuminate\Http\JsonResponse
{
    $data = $request->all();

    Log::info('вебхук получен', $data);

    // 1. /start от пользователя
    if (isset($data['message']) && isset($data['message']['text']) && $data['message']['text'] === '/start') {
        $chat = $data['message']['chat'];

        $recipient = Recipient::updateOrCreate(
            ['chat_id' => $chat['id']],
            [
                'first_name' => $chat['first_name'] ?? null,
                'last_name' => $chat['last_name'] ?? null,
                'username' => $chat['username'] ?? null,
                'is_active' => true,
            ]
        );

        Log::info('👤 Пользователь добавлен/обновлён', [
            'chat_id' => $chat['id'],
            'first_name' => $chat['first_name'] ?? null,
        ]);
    }

    // 2. Группа стала супергруппой
    if (isset($data['message']['migrate_to_chat_id'])) {
        $oldId = $data['message']['chat']['id'];
        $newId = $data['message']['migrate_to_chat_id'];

        AddressBook::where('chat_id', $oldId)
            ->update(['chat_id' => $newId]);

        Log::info('🔁 Группа обновлена: стала супергруппой', [
            'old_chat_id' => $oldId,
            'new_chat_id' => $newId,
        ]);
    }

    // 3. Пользователь вышел или заблокировал бота
    if (isset($data['my_chat_member'])) {
        $chatId = $data['my_chat_member']['chat']['id'];
        $status = $data['my_chat_member']['new_chat_member']['status'];

        if (in_array($status, ['kicked', 'left'])) {
            Recipient::where('chat_id', $chatId)
                ->update(
                    [
                        'is_active' => false,
                        'blocked_at' => now(),
                ]);

            Log::warning('🚫 Пользователь отключён', [
                'chat_id' => $chatId,
                'причина' => $status,
            ]);
        }
    }

return response()->json(['ok' => true]);  
}
}
