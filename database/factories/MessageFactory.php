<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'address_book_id' => null,
            'recipient_id' => null,
            'type' => 'message',
            'text' => $this->faker->sentence,
            'link' => null,
            'sent_at' => null,
        ];
    }
}
