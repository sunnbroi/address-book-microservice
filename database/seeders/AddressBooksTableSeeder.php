<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressBooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('address_books')->insert([
            'id' => '3425466f',
            'type' => 'manual',
            'client_key' => 'Jasjdkfnhjuasd',
            'name' => 'Test Address Book',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
