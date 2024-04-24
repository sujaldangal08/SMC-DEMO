<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'thumbnail_image' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
            'material_type' => $this->faker->word(),
            'stock' => $this->faker->numberBetween(1, 100),
            'cost_price' => $this->faker->randomFloat(2, 1, 100),
            'manufacturing' => $this->faker->word(),
            'supplier' => $this->faker->company(),
            'serial_number' => $this->faker->unique()->bothify('SN####'),
            'SKU_id' => \App\Models\Sku::factory(),
        ];
    }
}
