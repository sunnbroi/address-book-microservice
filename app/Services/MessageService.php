<?php
namespace App\Services;

use App\DTO\Message\StoreMessageDTO;
use App\Models\Message;
use App\Models\Recipient;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendMessageJob;
class MessageService
{
    public function createAndDispatch(StoreMessageDTO $dto): Message
{
    \Log::info('🔧 [Step 1] Начало метода createAndDispatch()', ['dto' => $dto]);

    return DB::transaction(function () use ($dto) {
        // 1. Создаём сообщение
        \Log::info('🔧 [Step 2] Пытаемся создать сообщение');

        $message = Message::create([
            'address_book_id' => $dto->addressBookId,
            'text' => $dto->text,
        ]);

        \Log::info('✅ [Step 2] Сообщение создано', ['message_id' => $message->id]);

        // 2. Получаем всех адресатов книги
        \Log::info('🔧 [Step 3] Получаем адресатов');

        $recipients = Recipient::query()
            ->where('address_book_id', $dto->addressBookId)
            ->whereNull('blocked_at')
            ->get();

        \Log::info('✅ [Step 3] Адресаты найдены', ['count' => $recipients->count()]);

        // 3. Добавляем задания в очередь
        foreach ($recipients as $recipient) {
            \Log::info('📦 [Step 4] Создаём задание на отправку', [
                'recipient_id' => $recipient->id,
                'message_id' => $message->id,
            ]);

            dispatch(new SendMessageJob($message, $recipient));
        }

        \Log::info('✅ [Step 5] Все задания созданы');

        return $message;
    });
}
}