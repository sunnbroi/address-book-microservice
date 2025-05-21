<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\DeliveryLog;
use Illuminate\Http\JsonResponse;

class MessageStatusController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $message = Message::findOrFail($id);

        $logs = DeliveryLog::where('message_id', $message->id)
            ->with('recipient:id,chat_id') // если есть связь
            ->get()
            ->map(function ($log) {
                return [
                    'recipient_id' => $log->recipient_id,
                    'status' => $log->status,
                    'delivered_at' => $log->delivered_at?->toDateTimeString(),
                    'attempts' => $log->attempts,
                    'error' => $log->error,
                ];
            });

        return response()->json([
            'message_id' => $message->id,
            'recipients' => $logs,
        ]);
    }
}
