<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Branch::factory()->create([
            'branch_name' => 'Demo Branch',
            'branch_street' => '123 Test St',
            'branch_street2' => 'Apt 1',
            'branch_city' => 'Demo City',
            'branch_state' => 'TS',
            'branch_zip' => '12345',
            'branch_phone' => '123-456-7890',
            'branch_email' => 'branch@email.com',
            'branch_code' => '123',
            'branch_status' => 'Active',
            'branch_country_id' => '1',
        ]);

        Branch::factory()->create([
            'branch_name' => 'vvvvv Branch',
            'branch_street' => '000 Test St',
            'branch_street2' => 'Apt 1',
            'branch_city' => 'ttt City',
            'branch_state' => 'TS',
            'branch_zip' => '12345',
            'branch_phone' => '123-456-7890',
            'branch_email' => 'branch@email.com',
            'branch_code' => '444',
            'branch_status' => 'Active',
            'branch_country_id' => '1',
        ]);

    }
}
