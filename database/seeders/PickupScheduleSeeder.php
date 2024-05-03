<?php

namespace Database\Seeders;

use App\Models\PickupSchedule;
use Illuminate\Database\Seeder;

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
            'status' => 'active',
            'notes' => 'Some notes',
            'materials' => ['Iron', 'Steel'],
            'amount' => [100, 200],
            'rate' => [10, 20],
            'weighing_type' => ['bridge', 'bridge'],
            'n_bins' => 2,
            'tare_weight' => [50, 60],
            'image' => ['image1.jpg', 'image2.jpg'],
            'coordinates' => ['27.688005', '85.335608'],
        ]);

        PickupSchedule::factory()->create([
            'route_id' => 1,
            'driver_id' => 2,
            'asset_id' => 1,
            'customer_id' => 4,
            'pickup_date' => now(),
            'status' => 'active',
            'notes' => 'Some notes',
            'materials' => ['Aluminum', 'Copper'],
            'amount' => [200, 300],
            'rate' => [20, 30],
            'weighing_type' => ['bridge', 'bridge'],
            'n_bins' => 2,
            'tare_weight' => [60, 70],
            'image' => ['image1.jpg', 'image2.jpg'],
            'coordinates' => ['27.701479', '85.340105'],
        ]);
    }
}
