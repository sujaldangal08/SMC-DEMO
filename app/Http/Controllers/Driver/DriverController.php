<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupSchedule;

class DriverController extends Controller {
    public function index()
{
    // Retrieve pickup schedules assigned to the logged-in driver with a specific status
    $pickupSchedules = auth()->user()->pickupSchedules()->where('status', 'status')->get();

    return view('driver.pickup-schedules.index', compact('pickupSchedules'));
}

}
