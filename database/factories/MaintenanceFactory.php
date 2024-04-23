<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => \App\Models\Asset::all()->random()->id,
            'maintenance_type' => $this->faker->word,
            'contact_meta' => json_encode([
                'contact_name' => $this->faker->name,
                'contact_phone' => $this->faker->phoneNumber,
            ]),
            'service_date' => $this->faker->date(),
        ];
    }
}
