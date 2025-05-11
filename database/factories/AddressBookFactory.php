<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;

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
            'invite_key' => $this->faker->regexify('[A-Z0-9]{10}') ,
            'client_key'=>Client::factory(),
            'name' => $this->faker->company(),
        ];
    }
}
