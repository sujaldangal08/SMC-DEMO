<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PickupSchedule;

class PickupScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PickupSchedule::factory()->create([
            'route_id' => 1,
            'driver_id' => 2,
            'asset_id' => 1,
            'customer_id' => 4,
            'pickup_date' => now(),
            'status' => 'pending',
            'notes' => 'Some notes',
            'materials' => ['Iron', 'Steel'],
            'amount' => [100, 200],
            'weighing_type' => ['Type 1', 'Type 2'],
            'n_bins' => 10,
            'tare_weight' => [50, 60],
            'image' => ['image1.jpg', 'image2.jpg'],
            'coordinates' => '0.0, 0.0',
        ]);
    }
}
