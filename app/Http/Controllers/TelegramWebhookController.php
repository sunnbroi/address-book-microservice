<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        if (! empty($data['message']['text']) && $data['message']['text'] === '/start') {
            $this->handleStartCommand($data['message']);
        }

        if (! empty($data['message']['migrate_to_chat_id'])) {
            $this->handleGroupMigration($data['message']);
        }

        if (! empty($data['my_chat_member'])) {
            $this->handleChatMemberUpdate($data['my_chat_member']);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleStartCommand(array $message): void
    {
        $chat = $message['chat'];

        Recipient::updateOrCreate(
            ['chat_id' => (string) $chat['id']],
            [
                'first_name' => $chat['first_name'] ?? null,
                'last_name' => $chat['last_name'] ?? null,
                'username' => $chat['username'] ?? null,
                'is_active' => true,
            ]
        );
    }

    protected function handleGroupMigration(array $message): void
    {
        $oldId = (string) $message['chat']['id'];
        $newId = (string) $message['migrate_to_chat_id'];

        AddressBook::where('chat_id', $oldId)->update(['chat_id' => $newId]);
    }

    protected function handleChatMemberUpdate(array $memberUpdate): void
    {
        $chat = $memberUpdate['chat'];
        $status = $memberUpdate['new_chat_member']['status'];
        $chatId = (string) $chat['id'];
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

        if (! $recipient || ! $timestamp) {
            return;
        }

        $updatedAt = $recipient->updated_at;

        if ($updatedAt && $updatedAt->timestamp >= $timestamp) {
            return;
        }

        if (in_array($status, ['kicked', 'left'])) {
            $recipient->is_active = false;
            $recipient->blocked_at = now();
            $recipient->save();
        }

        if ($status === 'member') {
            $recipient->is_active = true;
            $recipient->blocked_at = null;
            $recipient->save();
        }
    }

    protected function registerGroup(string $chatId, ?string $title): void
    {
        $clientKey = config('services.telegram.default_client_key');

        AddressBook::updateOrCreate(
            ['chat_id' => $chatId],
            [
                'name' => $title ?? 'Без названия',
                'client_key' => $clientKey,
                'deleted_at' => null,
            ]
        );
    }
}
