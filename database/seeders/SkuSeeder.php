<?php

namespace Database\Seeders;

use App\Models\Sku;
use Illuminate\Database\Seeder;

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
            'branch_id' => 1,
        ]);
    }
}
