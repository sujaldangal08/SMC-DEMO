<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;

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
            'meta' => json_encode([
                'brand' => 'Isuzu',
                'model' => 'N-series',
                'tare-weight' => '15 ton',
            ]),
            'branch_id' => 1,
        ]);

    }
}
