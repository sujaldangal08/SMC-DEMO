<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Route;



class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Route::create([
            'name' => 'Route Name', // Replace with actual route name
            'driver_id' => 2, // Driver ID is set to 2 as per your requirement
            'asset_id' => 1, // Replace with actual asset ID
            'description' => 'Route Description', // Replace with actual route description
            'status' => 'pending', // Replace with actual status
            'start_date' => now(), // Replace with actual start date
        ]);
    }
}
