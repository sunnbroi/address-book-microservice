<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('clients')->insert([
            'id'=> 'W23EWEEWQEFE',
            'host' => 'https://example.com',
            'client_key' => 'avenger@gmail.com',
            'secret_key' => 'sdkvfmsknjvmkis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
