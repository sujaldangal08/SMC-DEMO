<?php

namespace Database\Seeders;

use App\Models\Waste;
use Illuminate\Database\Seeder;

class WasteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Waste::factory()->create([
            'ticket_id' => 1,
            'quantity' => 100,
            'image' => 'waste_image.png',
            'notes' => 'Test waste',
        ]);
    }
}
