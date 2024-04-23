<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliverySchedule::create([
            'customer_id' => 1,
            'driver_id' => 1,
            'truck_id' => 1,
            'coordinates' => '40.712776, -74.005974',
            'materials' => 'Material Name',
            'amount' => 10,
            'n_trips' => 5,
            'n_trips_done' => 2,
            'interval' => 2,
            'start_date' => '2022-01-01',
            'delivery_date' => '2022-01-10',
            'end_date' => '2022-01-20',
            'status' => 'pending',
            'delivery_notes' => 'Delivery notes',
            'locale' => 'domestic',
            'meta' => json_encode(['location' => 'location1']),
        ]);
    }
}
