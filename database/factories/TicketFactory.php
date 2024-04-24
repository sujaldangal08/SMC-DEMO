<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rego_number' => $this->faker->unique()->numerify('REGO#####'),
            'driver_id' => 2,
            'customer_id' => 4,
            'route_id' => null, 
            'material' => $this->faker->word(),
            'initial_truck_weight' => $this->faker->randomNumber(),
            'full_bin_weight' => $this->faker->randomNumber(),
            'next_truck_weight' => $this->faker->randomNumber(),
            'tare_bin' => $this->faker->randomNumber(),
            'gross_weight' => $this->faker->randomNumber(),
            'notes' => $this->faker->sentence(),
            'image' => 'bin_image.png',
            'weighing_type' => $this->faker->randomElement(['bridge', 'pallet']),
            'ticked_type' => $this->faker->randomElement(['direct', 'schedule']),
            'lot_number' => $this->faker->word(),
            'ticket_number' => $this->faker->unique()->numerify('TICKET#####'),
            'in_time' => $this->faker->dateTime(),
            'out_time' => $this->faker->dateTime(),
        ];
    }
}
