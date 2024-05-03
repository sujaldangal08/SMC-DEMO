<?php

namespace Database\Seeders;

use App\Models\DeliveryTrip;
use Illuminate\Database\Seeder;

class DeliveryTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryTrip::factory()->create([
            'schedule_id' => 1,
            'driver_id' => 2,
            'truck_id' => 1,
            'trip_name' => 'Trip 1',
            'materials_loaded' => ['Material Name', 'Material Name 2', 'Material Name 3'],
            'amount_loaded' => [100, 200, 300],
            'trip_number' => 1,
            'status' => 'active',
            'note' => 'This is a more detailed note',
            'trip_date' => now(),
        ]);
    }
}
