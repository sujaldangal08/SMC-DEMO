<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+3 days');

        return [
            'status' => 'pending',
            'truck_id' => \App\Models\Truck::all()->random()->id,
            'driver_id' => \App\Models\Driver::all()->random()->id,
            'customer_id' => \App\Models\Customer::all()->random()->id,
            'delivery_location' => $this->faker->address,
            'delivery_start_date' => $startDate->format('Y-m-d'),
            'delivery_end_date' => $endDate->format('Y-m-d'),
            'delivery_start_time' => $this->faker->time(),
            'delivery_end_time' => $this->faker->time(),
            'delivery_file' => $this->faker->imageUrl(),
            'delivery_interval' => '3 days',
            'delivery_status' => 'pending',
            'delivery_notes' => $this->faker->sentence,
        ];
    }
}
