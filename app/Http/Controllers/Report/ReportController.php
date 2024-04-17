<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\PickupSchedule;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Asset;

class ReportController extends Controller {
    public function getTotalDeliveries() {
        $totalDeliveries = Delivery::count();
        return response()->json( [ 'total_deliveries' => $totalDeliveries ] );
    }

    // public function getTotalPickups() {
    //     $totalPickups = PickupSchedule::count(); //where('status', 'pending')->count();
    //     return response()->json( [ 'total_pickups' => $totalPickups ] );
    // }

public function getTotalPickups(Request $request) {
    // Retrieve the status from the request body
    $status = $request->input('status');

    // Query the PickupSchedule model based on the provided status
    $query = PickupSchedule::query();

    // If a status is provided, add a where clause to filter by that status
    if ($status) {
        $query->where('status', $status);
    }

    // Count the total number of pickup schedules based on the filtered query
    $totalPickups = $query->count();

    // Return the total count in a JSON response
    return response()->json(['total_pickups' => $totalPickups]);
}


    public function getTotalTickets() {
        $totalTickets = Ticket::count();
        return response()->json( [ 'total_tickets' => $totalTickets ] );
    }

    // public function getTotalUsers() {
    //     $totalUsers = User::where( 'role', 'customer' )->count();
    //     return response()->json( [ 'total_users' => $totalUsers ] );
    // }

    public function getTotalUsers() {
        $totalUsers = User::where( 'role_id', 2 )->count();
        // Assuming role_id 4 corresponds to the customer role
        return response()->json( [ 'total_users' => $totalUsers ] );
    }

    public function getTotalAssets() {
        $totalAssets = Asset::count();
        return response()->json( [ 'total_assets' => $totalAssets ] );
    }

}
