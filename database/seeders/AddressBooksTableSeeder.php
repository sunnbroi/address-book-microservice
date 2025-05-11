<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'id'=> 'ACDVSDVFDV',
            'invite_key' => 'FromSasha',
            'client_key' => 'avenger@gmail.com',
            'name' => 'firstAddressBook',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
