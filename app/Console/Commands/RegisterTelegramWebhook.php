<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegisterTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Регистрирует webhook для Telegram-бота';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $botToken = config('services.telegram.bot_token');
        $webhookUrl = config('services.telegram.webhook_url');

        $url = "https://api.telegram.org/bot{$botToken}/setWebhook";

        $response = Http::post($url, [
            'url' => "{$webhookUrl}/api/telegram/webhook",
        ]);

        if ($response->ok()) {
            $this->info('✅ Webhook успешно установлен: '.$webhookUrl);
        } else {
            $this->error('❌ Ошибка при установке Webhook: '.$response->body());
        }

        return self::SUCCESS;
    }
}
