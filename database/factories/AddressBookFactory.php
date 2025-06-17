<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AddressBook>
 */
class AddressBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invite_key' => $this->faker->regexify('[A-Z0-9]{10}'),
            'client_key' => Client::factory()->create()->client_key,
            'name' => $this->faker->company(),
        ];
    }
}
