<?php

namespace Tests\Unit\Services;

use App\Jobs\SendSingleTelegramMessageJob;
use App\Models\AddressBook;
use App\Models\Recipient;
use App\Services\MessageDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MessageDispatchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MessageDispatchService::class);
    }

    public function test_dispatch_to_recipient_only(): void
    {
        Queue::fake();

        $recipient = Recipient::factory()->create(['chat_id' => '1001']);

        $data = [
            'recipient_id' => $recipient->id,
            'type' => 'message',
            'text' => 'Hello!',
        ];

        $response = $this->service->dispatch($data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('queued', $response->getData(true)['status']);
        $this->assertEquals(1, $response->getData(true)['recipients_total']);

        Queue::assertPushed(SendSingleTelegramMessageJob::class, function ($job) use ($recipient) {
            $prop = new \ReflectionProperty($job, 'chatId');
            $prop->setAccessible(true);

            return $prop->getValue($job) === $recipient->chat_id;
        });
    }

    public function test_dispatch_to_address_book(): void
    {
        Queue::fake();

        $book = AddressBook::factory()->create(); // Убедись, что 'type' не нужен или добавлен в миграцию
        $recipients = Recipient::factory()->count(3)->create();
        $book->recipients()->attach($recipients);

        $data = [
            'address_book_id' => $book->id,
            'type' => 'message',
            'text' => 'Bulk send',
        ];

        $response = $this->service->dispatch($data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(3, $response->getData(true)['recipients_total']);
        $this->assertEquals('queued', $response->getData(true)['status']);

        Queue::assertPushed(SendSingleTelegramMessageJob::class, 3);
    }

    public function test_dispatch_fails_if_no_recipients(): void
    {
        $data = [
            'type' => 'message',
            'text' => 'Nobody here',
        ];

        $response = $this->service->dispatch($data);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['message' => 'Нет получателей для отправки'], $response->getData(true));
    }

    public function test_dispatch_handles_creation_exception(): void
    {
        $mock = $this->mock(MessageDispatchService::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $mock->shouldReceive('createMessage')
            ->andThrow(new \Exception('fail'));

        $response = $mock->dispatch([
            'recipient_id' => Str::uuid()->toString(),
            'type' => 'message',
            'text' => 'Will fail',
        ]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['message' => 'Ошибка при создании сообщения'], $response->getData(true));
    }
}
