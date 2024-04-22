<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            return [
                'title' => $this->faker->sentence,
                'image' => $this->faker->imageUrl(),
                'asset_type' => $this->faker->word,
                'meta' => $this->faker->sentence,
                'branch_id' => 1, // assuming a branch with id 1 exists
            ];
    }
}
