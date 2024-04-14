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
            'role' => 'admin',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'role' => 'driver',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'role' => 'manager',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'role' => 'customer',
            'max_login_attempts' => 5,
        ]);
        DB::table('roles')->insert([
            'role' => 'staff',
            'max_login_attempts' => 5,
        ]);
    }
}
