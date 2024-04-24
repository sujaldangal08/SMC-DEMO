<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Insurance;

class InsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Insurance::factory()->create([
            'asset_id' => 1, // assuming an asset with id 1 exists
            'insurance_type' => 'Comprehensive',
            'provider' => 'Insurance Company 1',
            'amount' => 10000,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'purchase_date' => now()->subMonth()->format('Y-m-d'),
            'attachment' => 'insurance1.pdf',
            'contact_meta' => json_encode([
                'contact_name' => 'John Doe',
                'contact_phone' => '1234567890',
            ]),
        ]);
    }
}
