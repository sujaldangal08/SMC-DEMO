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
        DeliveryTrip::create([
            'schedule_id' => 1,
            'driver_id' => 1, // Replace with actual driver ID
            'truck_id' => 1, // Replace with actual truck ID
            'materials_loaded' => 'Material Name', // Replace with actual material name
            'amount_loaded' => 100, // Replace with actual amount
            'trip_number' => 'Trip 001', // Replace with actual trip number
            'status' => 'pending', // Replace with actual status
            'trip_date' => now(), // Replace with actual trip date
        ]);
    }
}
