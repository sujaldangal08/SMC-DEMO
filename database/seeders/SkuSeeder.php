<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sku;

class SkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sku::factory()->create([
            'SKU' => 'SKU0001',
            'name' => 'Name1',
            'barcode' => 'BAR0001',
            'tags' => 'Tag1',
            'status' => 'active',
        ]);
    }
}
