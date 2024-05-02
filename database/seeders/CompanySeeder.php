<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Company::factory()->create([
            'company_name' => 'Demo Company',
            'company_street' => '123 Test St',
            'company_street2' => 'Apt 1',
            'company_city' => 'Demo City',
            'company_state' => 'TS',
            'company_zip' => '12345',
            'company_phone' => '123-456-7890',
            'company_email' => 'company@email.com',
            'company_code' => '123',
            'company_country_id' => '1',
        ]);
    }
}
