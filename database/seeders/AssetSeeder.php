<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('assets')->insert([
            'title' => 'Asset 1',
            'image' => 'image.jpg',
            'asset_type' => 'type',
            'meta' => json_encode([
                'description' => 'This is a valuable asset',
                'location' => 'Warehouse 1',
                'condition' => 'New',
                'purchase_date' => now()->format('Y-m-d'),
            ]),
            'branch_id' => 1, // assuming a branch with id 1 exists
        ]);
    }
}
