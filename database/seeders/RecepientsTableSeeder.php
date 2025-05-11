<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecepientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('recipients')->insert([
            'id'=> 'ASCXSSDVSDFCD',
            'telegram_user_id' => 'Rowssdxz',
            'username' => 'tfgegbdxbvf',
            'first_name' => 'wqD',
            'last_name' => 'lASDFFD',
            'type' => 'chat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
