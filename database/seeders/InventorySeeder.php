<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::factory()->create([
            'name' => 'Inventory1',
            'thumbnail_image' => 'https://example.com/image.jpg',
            'description' => 'This is a sample description.',
            'material_type' => 'Material1',
            'stock' => 10,
            'cost_price' => 50.00,
            'manufacturing' => 'Manufacturing1',
            'supplier' => 'Supplier1',
            'serial_number' => 'SN0001',
            'SKU_id' => 1, // assuming a SKU with id 1 exists
        ]);

    }
}
