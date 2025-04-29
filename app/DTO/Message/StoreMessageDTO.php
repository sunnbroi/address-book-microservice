<?php

namespace App\DTO\Message;

use App\Models\Client;
use App\Http\Requests\Message\StoreMessageRequest;

class StoreMessageDTO
{
    public function __construct(
        public string $addressBookId,
        public string $text,
        public Client $client
    ) {}

    /**
     * Используется в контроллере — принимает FormRequest
     */
    public static function fromRequest(StoreMessageRequest $request): self
    {
        return new self(
            addressBookId: $request->input('address_book_id'),
            text: $request->input('text'),
            client: $request->get('auth_client'),
        );
    }

    /**
     * Используется в тестах, очередях, Tinker
     */
    public static function fromArray(array $data, Client $client): self
    {
        return new self(
            addressBookId: $data['address_book_id'],
            text: $data['text'],
            client: $client,
        );
    }
}
