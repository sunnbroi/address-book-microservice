<?php

namespace App\Jobs;

use App\Models\DeliveryLog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class LogDeliveryResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $logBatch;

    public function __construct(array $logBatch)
    {
        $this->logBatch = $logBatch;
    }

    public function handle(): void
    {
        if (empty($this->logBatch)) {
            return;
        }

        try {
            DeliveryLog::upsert(
                $this->logBatch,
                ['message_id', 'recipient_id'],
                ['status', 'error', 'attempts', 'delivered_at', 'error', 'updated_at']
            );

            Log::info('Логи доставки успешно сохранены', ['count' => count($this->logBatch)]);
        } catch (\Throwable $e) {
            Log::error('Ошибка при записи логов доставки', [
                'error' => $e->getMessage(),
                'logs' => $this->logBatch,
            ]);
        }
    }
}
