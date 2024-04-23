<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class DeliveyScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\User::all()->random()->id,
            'driver_id' => \App\Models\User::all()->random()->id,
            'truck_id' => \App\Models\Asset::all()->random()->id,
            'coordinates' => $this->faker->latitude.', '.$this->faker->longitude,
            'materials' => $this->faker->word,
            'amount' => $this->faker->randomNumber(2),
            'n_trips' => $this->faker->numberBetween(1, 10),
            'n_trips_done' => $this->faker->numberBetween(0, 10),
            'interval' => $this->faker->numberBetween(0, 10),
            'start_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'delivery_notes' => $this->faker->sentence,
            'locale' => $this->faker->randomElement(['domestic', 'international']),
            'meta' => json_encode(['key' => $this->faker->word, 'value' => $this->faker->word]),
        ];
    }
}
