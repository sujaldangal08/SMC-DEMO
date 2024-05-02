<?php

use App\Models\Branch;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

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
    Log::info('ScheduleCommand executed successfully.'.now());
})->everyMinute();

Schedule::call(function () {

    Artisan::call('app:schedule-command');
})->everyMinute();

Schedule::command('app:make-delivery-trips')
    ->everyFiveSeconds()
    ->appendOutputTo(storage_path('logs/delivery-trips.log'));

Schedule::command('sanctum:prune-expired --hours=24')->daily();

Schedule::command('app:execute-task')
        ->everyMinute()
        ->onFailure(function (\Exception $exception) {
            Log::error('Failed to execute app:execute-task command');
        });

