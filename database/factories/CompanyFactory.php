<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company,
            'company_street' => $this->faker->streetAddress,
            'company_street2' => $this->faker->secondaryAddress,
            'company_city' => $this->faker->city,
            'company_state' => $this->faker->state,
            'company_zip' => $this->faker->postcode,
            'company_phone' => $this->faker->phoneNumber,
            'company_email' => $this->faker->companyEmail,
            'company_code' => $this->faker->randomNumber(3, true),
            'company_country_id' => $this->faker->randomNumber(2, true)
        ];
    }
}
