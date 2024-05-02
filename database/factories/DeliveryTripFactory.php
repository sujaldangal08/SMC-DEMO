<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\DeliverySchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryTrip>
 */
class DeliveryTripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => DeliverySchedule::factory(),
            'driver_id' => User::factory(),
            'truck_id' => Asset::factory(),
            'materials_loaded' => $this->faker->word,
            'amount_loaded' => $this->faker->randomNumber(),
            'trip_number' => $this->faker->unique()->numerify('Trip ###'),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'trip_date' => $this->faker->date(),
        ];
    }
}
