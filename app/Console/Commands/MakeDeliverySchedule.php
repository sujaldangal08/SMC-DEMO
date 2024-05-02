<?php

namespace App\Console\Commands;

use App\Models\DeliverySchedule;
use Illuminate\Console\Command;

class MakeDeliverySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-delivery-trips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command create a delivery schedule for the day.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all the delivery schedules for the day that are pending
        $deliverySchedules = DeliverySchedule::where('status', 'in_progress')->get();

        // Loop through the delivery schedules
        foreach ($deliverySchedules as $schedule) {

            // Check if the schedule is completed
            if ($schedule->is_completed) {
                // Skip this schedule since it's already completed
                continue;
            }
            // dd($schedule);
            // Check if today is one of the delivery dates for this schedule
            $deliveryDates = $schedule->delivery_date;
            // Check if today is one of the delivery dates for this schedule ignore the first date as it's the start date and the trip for that date has already been created
            if (in_array(date('Y-m-d'), array_slice($deliveryDates, 1))) {
                // Create a delivery trip
                $schedule->createDeliveryTrip(date('Y-m-d'));
            }
        }
    }
}
