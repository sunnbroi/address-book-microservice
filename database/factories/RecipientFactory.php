<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipient>
 */
class RecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_user_id' => $this->faker->unique()->uuid(),
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'type' => $this->faker->randomElement(['user', 'group', 'channel']),
        ];
    }
}
