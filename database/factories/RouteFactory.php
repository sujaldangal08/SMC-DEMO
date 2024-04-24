<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->streetName(),
            'driver_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'active', 'done', 'unloading', 'full', 'cancelled']),
            'start_date' => $this->faker->date(),
        ];
    }
}
