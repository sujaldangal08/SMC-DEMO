<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ticket::factory()->create([
            'rego_number' => 'REGO12345',
            'driver_id' => 2,
            'customer_id' => 4,
            'route_id' => 1,
            'material' => 'Material Name',
            'initial_truck_weight' => 1000,
            'full_bin_weight' => 2000,
            'next_truck_weight' => 1500,
            'tare_bin' => 500,
            'gross_weight' => 2500,
            'notes' => 'Test ticket',
            'image' => 'bin_image.png',
            'weighing_type' => 'bridge',
            'ticked_type' => 'direct',
            'lot_number' => 'LOT123',
            'ticket_number' => 'TICKET12345',
            'in_time' => now(),
            'out_time' => now()->addHours(1), 
        ]);
    }
}
