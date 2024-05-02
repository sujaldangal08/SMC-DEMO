<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PickupSchedule>
 */
class PickupScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'route_id' => Route::factory(),
            'driver_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'customer_id' => User::factory(),
            'pickup_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'active', 'inactive', 'done', 'unloading', 'full', 'cancelled']),
            'notes' => $this->faker->sentence(),
            'materials' => $this->faker->word(),
            'amount' => $this->faker->randomNumber(),
            'weighing_type' => $this->faker->word(),
            'n_bins' => $this->faker->randomNumber(),
            'tare_weight' => $this->faker->randomNumber(),
            'image' => $this->faker->imageUrl(),
            'coordinates' => $this->faker->latitude().', '.$this->faker->longitude(),
        ];

    }
}
