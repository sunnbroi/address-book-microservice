<?php

namespace Database\Factories;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryLog>
 */
class DeliveryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address_book_id' => AddressBook::factory(),
            'recipient_id' => Recipient::factory(),
            'status' => $this->faker->randomElement(['success', 'failed', 'pending']),
            'error' => $this->faker->boolean(20) ? $this->faker->sentence() : null, // 20% ошибок
            'attempts' => $this->faker->numberBetween(1, 3),
            'sent_at' => $this->faker->dateTimeBetween('-1 days', 'now'),
            'delivered_at' => $this->faker->boolean(70) ? now() : null, // 70% успешных доставок
        ];
    }
}
