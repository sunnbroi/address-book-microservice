<?php

namespace App\Jobs;

use App\Models\DeliveryLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        } catch (\Throwable $e) {
        }
    }
}
