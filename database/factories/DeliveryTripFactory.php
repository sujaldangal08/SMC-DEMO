<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\DeliverySchedule;
use App\Models\DeliveryTrip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DeliveryTripFactory extends Factory
{
    protected $model = DeliveryTrip::class;

    public function definition(): array
    {
        return [
            'materials_loaded' => $this->faker->word(),
            'amount_loaded' => $this->faker->word(),
            'trip_number' => $this->faker->word(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'schedule_id' => DeliverySchedule::factory(),
            'driver_id' => User::factory(),
            'truck_id' => Asset::factory(),
        ];
    }
}
