<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Xero\XeroTenant>
 */
class XeroTenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'connection_id' => Str::random(10),
            'authEventId' => Str::random(10),
            'tenantId' => Str::random(10),
            'tenantType' => $this->faker->word,
            'tenantName' => $this->faker->word,
            'xero_connect_id' => 1, // Assuming there is a XeroConnect with id 1
            'createdDateUtc' => now(),
            'updatedDateUtc' => now(),
        ];

    }
}
