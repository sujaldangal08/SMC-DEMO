<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;




class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Delivery::factory()->create([
            'status' => 'pending',
            'truck_id' => 1,
            'driver_id' => 2,
            'customer_id' => 4, 
            'delivery_location' => '123 Main St, Anytown, USA',
            'delivery_start_date' => now()->format('Y-m-d'),
            'delivery_end_date' => now()->addDays(3)->format('Y-m-d'),
            'delivery_start_time' => '08:00:00',
            'delivery_end_time' => '17:00:00',
            'delivery_file' => 'delivery1.pdf',
            'delivery_interval' => '3 days',
            'delivery_status' => 'pending',
            'delivery_notes' => 'Delivery notes here',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
