<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Route::factory()->create([
            'name' => 'Route Name', // Replace with actual route name
            'driver_id' => 2, // Driver ID is set to 2 as per your requirement
            'asset_id' => 1, // Replace with actual asset ID
            'description' => 'Route Description', // Replace with actual route description
            'status' => 'active', // Replace with actual status
            'start_date' => now(), // Replace with actual start date
        ]);
    }
}
