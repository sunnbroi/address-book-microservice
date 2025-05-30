<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('clients')->insert([
            'client_key' => (string) Str::uuid(),
            'name' => 'First Client',
            'secret_key' => Str::random(32),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
