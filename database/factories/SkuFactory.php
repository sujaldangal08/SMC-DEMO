<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sku>
 */
class SkuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'SKU' => $this->faker->unique()->bothify('SKU####'),
            'name' => $this->faker->word(),
            'barcode' => $this->faker->unique()->bothify('BAR####'),
            'tags' => $this->faker->word(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
