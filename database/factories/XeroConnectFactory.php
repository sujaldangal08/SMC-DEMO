<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\XeroConnect>
 */
class XeroConnectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_token' => Str::random(10),
            'access_token' => Str::random(10),
            'expires_in' => $this->faker->randomDigitNotNull,
            'token_type' => $this->faker->word,
            'refresh_token' => Str::random(10),
            'scope' => $this->faker->word,
        ];
    }
}
