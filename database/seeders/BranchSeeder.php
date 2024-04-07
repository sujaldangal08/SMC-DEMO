<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

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
            'company_id'=>'1'
        ]);
    }
}
