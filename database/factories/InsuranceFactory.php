<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Insurance>
 */
class InsuranceFactory extends Factory
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
            'insurance_type' => $this->faker->word,
            'provider' => $this->faker->company,
            'amount' => $this->faker->randomNumber(5),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'purchase_date' => $this->faker->date(),
            'attachment' => $this->faker->imageUrl(),
            'contact_meta' => json_encode([
                'contact_name' => $this->faker->name,
                'contact_phone' => $this->faker->phoneNumber,
            ]),
        ];
    }
}
