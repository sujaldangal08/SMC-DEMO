<?php

namespace Database\Factories;

use App\Models\DeliverySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DeliveryScheduleFactory extends Factory
{
    protected $model = DeliverySchedule::class;

    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->randomNumber(),
            'driver_id' => $this->faker->randomNumber(),
            'truck_id' => $this->faker->randomNumber(),
            'coordinates' => $this->faker->words(),
            'materials' => $this->faker->words(),
            'amount' => $this->faker->words(),
            'n_trips' => $this->faker->randomNumber(),
            'n_trips_done' => $this->faker->randomNumber(),
            'interval' => $this->faker->randomNumber(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'status' => $this->faker->word(),
            'delivery_notes' => $this->faker->word(),
            'meta' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
