<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'branch_name' => $this->faker->company,
            'branch_street' => $this->faker->streetAddress,
            'branch_street2' => $this->faker->secondaryAddress,
            'branch_city' => $this->faker->city,
            'branch_state' => $this->faker->state,
            'branch_zip' => $this->faker->postcode,
            'branch_phone' => $this->faker->phoneNumber,
            'branch_email' => $this->faker->companyEmail,
            'branch_code' => $this->faker->randomNumber(3, true),
            'branch_status' => 'Active',
            'branch_country_id' => $this->faker->randomNumber(2, true),
            'company_id'=>\App\Models\Company::all()->random()->id,
        ];
    }
}
