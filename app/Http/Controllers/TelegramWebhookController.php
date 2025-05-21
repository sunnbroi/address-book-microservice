<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Models\Recipient;
use App\Models\AddressBook;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!empty($data['message']['text']) && $data['message']['text'] === '/start') {
            $this->handleStartCommand($data['message']);
        }

        if (!empty($data['message']['migrate_to_chat_id'])) {
            $this->handleGroupMigration($data['message']);
        }

        if (!empty($data['my_chat_member'])) {
            $this->handleChatMemberUpdate($data['my_chat_member']);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleStartCommand(array $message): void
    {
        $chat = $message['chat'];

        $recipient = Recipient::updateOrCreate(
            ['chat_id' => (string) $chat['id']],
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

    protected function handleGroupMigration(array $message): void
    {
        $oldId = (string) $message['chat']['id'];
        $newId = (string) $message['migrate_to_chat_id'];

        $updated = AddressBook::where('chat_id', $oldId)->update(['chat_id' => $newId]);

        if ($updated) {
            Log::info('🔁 Группа стала супергруппой', [
                'old_chat_id' => $oldId,
                'new_chat_id' => $newId,
            ]);
        } else {
            Log::warning('⚠️ Не найдена группа для обновления', ['old_chat_id' => $oldId]);
        }
    }

    protected function handleChatMemberUpdate(array $memberUpdate): void
    {
        $chat     = $memberUpdate['chat'];
        $status   = $memberUpdate['new_chat_member']['status'];
        $chatId   = (string) $chat['id'];
        $chatType = $chat['type'] ?? null;
        $chatTitle = $chat['title'] ?? null;
        $timestamp = $memberUpdate['date'] ?? null;

        if ($chatType === 'private') {
            $this->handlePrivateChatStatus($chatId, $status, $timestamp);
        }

        if (in_array($chatType, ['group', 'supergroup']) && in_array($status, ['member', 'administrator'])) {
            $this->registerGroup($chatId, $chatTitle);
        }
    }

    protected function handlePrivateChatStatus(string $chatId, string $status, ?int $timestamp): void
    {
        $recipient = Recipient::where('chat_id', $chatId)->first();

        if (!$recipient || !$timestamp) {
            return;
        }

        $eventTime = Carbon::createFromTimestamp($timestamp);
        $updatedAt = $recipient->updated_at;

        if ($updatedAt && $updatedAt->timestamp >= $timestamp) {
            Log::info('⏳ Устаревшее событие Telegram', [
                'chat_id' => $chatId,
                'время_вебхука' => $eventTime->toDateTimeString(),
                'время_в_базе' => $updatedAt->toDateTimeString(),
            ]);
            return;
        }

        if (in_array($status, ['kicked', 'left'])) {
            $recipient->update([
                'is_active' => false,
                'blocked_at' => now(),
            ]);
            Log::warning('🚫 Пользователь отключил бота', ['chat_id' => $chatId, 'причина' => $status]);
        }

        if ($status === 'member') {
            $recipient->update([
                'is_active' => true,
                'blocked_at' => null,
            ]);
            Log::info('✅ Пользователь снова активен', ['chat_id' => $chatId]);
        }
    }

    protected function registerGroup(string $chatId, ?string $title): void
    {
        $clientKey = config('services.telegram.default_client_key');

        AddressBook::updateOrCreate(
            ['chat_id' => $chatId, 'type' => 'telegram'],
            [
                'name' => $title ?? 'Без названия',
                'client_key' => $clientKey,
                'deleted_at' => null,
            ]
        );

        Log::info('✅ Бот добавлен в Telegram-группу', [
            'chat_id' => $chatId,
            'title' => $title,
        ]);
    }
}
