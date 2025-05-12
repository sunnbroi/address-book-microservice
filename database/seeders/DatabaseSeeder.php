<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AddressBook;
use App\Models\Recipient;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Client::factory(10)->create()->each(function ($client) {
            AddressBook::factory()
                ->count(rand(2, 5))
                ->create([
                    'client_key' => $client->client_key,
                ])
                ->each(function ($addressBook) {
                    $recipients = Recipient::factory()
                        ->count(rand(3, 5))
                        ->create();
                    $addressBook->recipients()->attach($recipients->pluck('id'));
                });
        });
}
}
