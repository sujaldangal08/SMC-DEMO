<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * in order to run this seeder, you need to run the following command:
     * php artisan db:seed --class=BackendSeeder
     */
    public function run(): void
    {
        DB::table('backends')->insert([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
    }
}
