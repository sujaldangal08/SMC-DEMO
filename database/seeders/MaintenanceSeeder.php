<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Maintenance;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Maintenance::factory()->create([
            'asset_id' => 1, // assuming an asset with id 1 exists
            'maintenance_type' => 'Regular Checkup',
            'contact_meta' => json_encode([
                'contact_name' => 'John Doe',
                'contact_phone' => '1234567890',
            ]),
            'service_date' => now()->format('Y-m-d'),
        ]);
    }
}
