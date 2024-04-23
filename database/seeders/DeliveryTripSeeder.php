<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryTrip;


class DeliveryTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryTrip::create([
            'schedule_id' => 1,
            'driver_id' => 2,
            'truck_id' => 1,
            'materials_loaded' => 'Material Name',
            'amount_loaded' => 100,
            'trip_number' => 'Trip 001',
            'status' => 'pending',
            'trip_date' => now(),
        ]);
    }
}
