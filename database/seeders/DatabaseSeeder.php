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
        Client::factory(2)->create()->each(function ($client) {
            AddressBook::factory()
                ->count(2)
                ->create([
                    'client_key' => $client->client_key,
                ])
                ->each(function ($addressBook) {
                    $recipients = Recipient::factory()
                        ->count(rand(100, 110))
                        ->create();
                    $addressBook->recipients()->attach($recipients->pluck('id'));
                });
        });
}
}
