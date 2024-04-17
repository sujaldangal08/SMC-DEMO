<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliverySchedule;

class MakeDeliverySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-delivery-schedule';

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
    }
}
