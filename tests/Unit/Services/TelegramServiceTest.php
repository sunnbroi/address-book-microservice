<?php

namespace Tests\Unit\Services;

use App\Services\TelegramService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    protected TelegramService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TelegramService::class);
    }

    public function test_handle_response_returns_data_on_success()
    {
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('successful')->andReturn(true);
        $mockResponse->shouldReceive('json')->andReturn([
            'ok' => true,
            'result' => 'mocked',
        ]);

        $result = $this->invokeHandleResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertEquals('mocked', $result['result']);
    }

    public function test_handle_response_throws_exception_on_failure()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Telegram API error');

        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('successful')->andReturn(false);
        $mockResponse->shouldReceive('json')->andReturn([
            'ok' => false,
            'error' => 'something went wrong',
        ]);

        $this->invokeHandleResponse($mockResponse);
    }

    protected function invokeHandleResponse($mockResponse): array
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('handleResponse');
        $method->setAccessible(true);

        return $method->invoke($this->service, $mockResponse);
    }

    public function test_send_message_success()
    {
        $chatId = Str::uuid()->toString();
        $text = 'Hello, test!';

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 123,
                    'chat' => ['id' => $chatId],
                    'text' => $text,
                ],
            ]),
        ]);

        $result = $this->service->sendMessage($chatId, $text);

        $this->assertTrue($result['ok']);
        $this->assertEquals($text, $result['result']['text']);
    }

    public function test_send_message_failure()
    {
        $chatId = Str::uuid()->toString();
        $text = 'Failure test';

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => false,
                'description' => 'Forbidden',
            ], 403),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Telegram API error');

        $this->service->sendMessage($chatId, $text);
    }

    public function test_is_valid_chat_id_returns_true_when_ok()
    {
        $chatId = '123456';

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['id' => $chatId],
            ]),
        ]);

        $this->assertTrue($this->service->isValidChatId($chatId));
    }

    public function test_is_valid_chat_id_returns_false_on_failure()
    {
        $chatId = '123456';

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => false,
            ], 403),
        ]);

        $this->assertFalse($this->service->isValidChatId($chatId));
    }

    public function test_is_valid_chat_id_returns_false_on_exception()
    {
        $chatId = '123456';

        Http::fake([
            'https://api.telegram.org/*' => Http::response(null, 500),
        ]);

        $this->assertFalse($this->service->isValidChatId($chatId));
    }

    public function test_send_media_with_valid_url_downloads_and_sends_file()
    {
        $chatId = '123456';
        $type = 'video';
        $url = 'https://example.com/test.mp4';
        $caption = 'Check this out!';

        Http::fake([
            $url => Http::response('video-bytes', 200),
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => 'media sent',
            ]),
        ]);

        $result = $this->service->sendMedia($type, $chatId, $url, $caption);

        $this->assertTrue($result['ok']);
        $this->assertEquals('media sent', $result['result']);
    }

    public function test_send_media_throws_exception_on_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->sendMedia('image', '123', 'fake.jpg');
    }

    public function test_send_media_throws_exception_on_missing_local_file()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found');

        $this->service->sendMedia('photo', '123', 'nonexistent_file.jpg');
    }
}
