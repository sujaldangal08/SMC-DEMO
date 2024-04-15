<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();



Artisan::command('app:schedule-command', function () {
    Log::info('ScheduleCommand is running...');

    // Retrieve the record you want to update
    $branch = Branch::find('1');

    // Check if the record exists
    if ($branch) {
        // Set the properties you want to update
        $branch->branch_name = 'New Name';

        // Update the record in the database
        $branch->save();

        $this->info('Branch name updated successfully.');
    } else {
        $this->error('Branch not found.');
    }

    // Log a message indicating the completion of the command
    Log::info('ScheduleCommand executed successfully.' . now());
})->everyMinute();


Schedule::call(function () {

    Artisan::call('app:schedule-command');
})->everyMinute();




