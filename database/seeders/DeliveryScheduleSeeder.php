<?php

namespace Database\Seeders;

use App\Models\DeliverySchedule;
use Illuminate\Database\Seeder;

class DeliveryScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliverySchedule::factory()->create([
            'customer_id' => 4,
            'driver_id' => 2,
            'truck_id' => 1,
            'coordinates' => ['40.712776, -74.005974'],
            'materials' => ['Aluminum', 'Copper'],
            'amount' => [500, 300],
            'n_trips' => 5,
            'n_trips_done' => 2,
            'interval' => 2,
            'start_date' => '2022-01-10',
            'delivery_date' => ['2022-01-10', '2022-01-15'],
            'end_date' => '2022-01-15',
            'status' => 'pending',
            'delivery_notes' => 'Delivery notes',
            'locale' => 'domestic',
            'meta' => json_encode([
                'pickup_location' => 'London, UK',
                'delivery_location' => 'Manchester, UK',
                'special_instructions' => 'Deliver between 9am to 5pm',
                'contact_person' => 'Jane Doe',
                'contact_phone' => '+44 20 1234 5678',]),
        ]);
        DeliverySchedule::factory()->create([
            'customer_id' => 7,
            'driver_id' => 6,
            'truck_id' => 2,
            'coordinates' => ['34.052235', '-118.243683'],
            'materials' => ['Steel', 'Iron'],
            'amount' =>  [1000, 800],
            'n_trips' => 10,
            'n_trips_done' => 5,
            'interval' => 0,
            'start_date' => '2022-02-10',
            'delivery_date' => ['2022-02-10', '2022-02-15'],
            'end_date' => '2022-02-15',
            'status' => 'completed',
            'delivery_notes' => 'some delivery notes',
            'locale' => 'international',
            'meta' => json_encode([
                'pickup_location' => 'Los Angeles, CA',
                'delivery_location' => 'Tokyo, Japan',
                'special_instructions' => 'Handle with care, fragile materials',
                'contact_person' => 'John Doe',
                'contact_phone' => '+1 234 567 890',]),
        ]);
    }
}
