<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [1, 2, 3, 4, 5]; // assuming these are the IDs of the roles

        foreach ($roles as $roleId) {
            User::factory()->create([
                'name' => 'User '.$roleId,
                'email' => 'user'.$roleId.'@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roleId,
            ]);
        }
        User::factory()->create([
            'name' => 'User 6',
            'email' => 'user6@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'User 7',
            'email' => 'user7@example.com',
            'password' => Hash::make('password'),
            'role_id' => 4,
        ]);
    }
}
