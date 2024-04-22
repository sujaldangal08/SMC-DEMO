<?php

namespace Database\Factories;

use App\Models\Delivery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->word(),
            'truck_id' => $this->faker->randomNumber(),
            'driver_id' => $this->faker->randomNumber(),
            'customer_id' => $this->faker->randomNumber(),
            'delivery_location' => $this->faker->word(),
            'delivery_start_date' => $this->faker->word(),
            'delivery_end_date' => $this->faker->word(),
            'delivery_start_time' => $this->faker->word(),
            'delivery_end_time' => $this->faker->word(),
            'delivery_file' => $this->faker->word(),
            'delivery_interval' => $this->faker->word(),
            'delivery_status' => $this->faker->word(),
            'delivery_notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
