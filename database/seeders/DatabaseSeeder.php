<?php

namespace Database\Seeders;

use App\Models\AddressBook;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Client;
use App\Models\Recipient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    /**
     * Главный сидер для тестов и наполнения БД.
     *
     * 💡 Если вы просто начинаете разработку — используйте только ClientsTableSeeder.
     *   php artisan db:seed --class=ClientsTableSeeder
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
