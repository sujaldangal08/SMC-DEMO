<?php

namespace App\Http\Controllers\Driver;

use Illuminate\Http\Request;
use App\Models\PickupSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller {

    public function viewAssignedPickups()
    {
        $driver = Auth::user(); // Assuming driver is logged in
        $pickups = PickupSchedule::where('driver_id', $driver->id)->get();
        return response()->json($pickups, 200);
    }

    public function markPickupAsDone($pickupId)
    {
        $driver = Auth::user(); // Assuming driver is logged in
        $pickup = PickupSchedule::find($pickupId);
        if ($pickup && $pickup->driver_id == $driver->id) {
            $pickup->status = 'done';
            $pickup->save();
            return response()->json(['message' => 'Pickup marked as done'], 200);
        } else {
            return response()->json(['message' => 'Unauthorized or Pickup not found'], 401);
        }
    }

    // Add more methods for handling other operations like validation in N-bins, adding notes, etc.

    public function logout()
    {
        Auth::logout(); // Logs out the currently authenticated user
        return response()->json(['message' => 'Logout successful'], 200);
    }

}
