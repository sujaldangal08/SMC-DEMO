<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'name' => 'admin',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'name' => 'driver',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'name' => 'manager',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'name' => 'customer',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'name' => 'staff',
            'max_login_attempts' => 5,
        ]);
    }
}
