<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Asset::factory()->create([
            'title' => 'Asset',
            'image' => 'image.jpg',
            'asset_type' => 'Vehicle',
            'rego_number' => 'KJH-1234',
            'meta' => json_encode([
                'brand' => 'Volvo',
                'model' => 'FH16',
                'engine' => '16-liter turbocharged diesel',
                'horsepower' => '750',
                'torque' => '3550 Nm',
                'payload_capacity' => '40 ton',
            ]),
            'branch_id' => 1,
        ]);
        Asset::factory()->create([
            'title' => 'Recyvle',
            'image' => 'image.jpg',
            'rego_number' => 'LJH-1234',
            'asset_type' => 'Vehicle',
            'meta' => json_encode([
                'brand' => 'Mercedes-Benz',
                'model' => 'Actros',
                'engine' => '12.8-liter turbocharged diesel',
                'horsepower' => '625',
                'torque' => '3000 Nm',
                'payload_capacity' => '36 ton',
            ]),
            'branch_id' => 2,
        ]);
    }
}
