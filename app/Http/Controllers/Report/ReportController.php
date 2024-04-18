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

    // public function getTotalDeliveries() {
    //     $totalDeliveries = Delivery::count();
    //     return response()->json( [ 'total_deliveries' => $totalDeliveries ] );
    // }

    // public function getTotalDeliveries(Request $request) {
    //     // Retrieve the status from the request body
    //     $status = $request->input('status');

    //     // Query the Delivery model based on the provided status
    //     $query = Delivery::query();

    //     // If a status is provided, add a where clause to filter by that status
    //     if ($status) {
    //         $query->where('status', $status);
    //     }

    //     // Count the total number of deliveries based on the filtered query
    //     $totalDeliveries = $query->count();

    //     // Return the total count in a JSON response
    //     return response()->json(['total_deliveries' => $totalDeliveries]);
    // }

    public function getTotalDeliveries(Request $request) {
        try {
        // Validate the request data
        $request->validate([
            'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled'
        ]);

        // Retrieve the validated status from the request
        $status = $request->input('status');

        // Build the query to count deliveries
        $query = Delivery::query();

        // If a status is provided, add a where clause to filter by that status
        if ($status) {
            $query->where('status', $status);
        }

        // Count the total number of deliveries based on the filtered query
            $totalDeliveries = $query->count();
        } catch (\Exception $e) {
            // Handle database query errors
            return response()->json(['error' => 'Data not found!'], 500);
        }

        // Return the total count in a JSON response
        return response()->json(['total_deliveries' => $totalDeliveries]);
    }


    public function getTotalPickups(Request $request) {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,schedule,unloading,done,completed,cancelled'
            ]);

            $status = $request->input('status');

            $query = PickupSchedule::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalPickups = $query->count();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found!'], 500);
        }

        return response()->json(['total_pickups' => $totalPickups]);
    }


    public function getTotalTickets(Request $request) {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled'
            ]);

            $status = $request->input('status');

            $query = Ticket::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalTickets = $query->count();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found!'], 500);
        }

        return response()->json(['total_tickets' => $totalTickets]);
    }


    // public function getTotalUsers() {
    //     $totalUsers = User::where( 'role_id', 2 )->count();
    //     // Assuming role_id 4 corresponds to the customer role
    //     return response()->json( [ 'total_users' => $totalUsers ] );
    // }

    public function getTotalUsers(Request $request) {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive'
            ]);

            $status = $request->input('status');

            $query = User::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalUsers = $query->count();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found!'], 500);
        }

        return response()->json(['total_users' => $totalUsers]);
    }


    public function getTotalAssets(Request $request) {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled'
            ]);

            $status = $request->input('status');

            $query = Asset::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalAssets = $query->count();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found!'], 500);
        }

        return response()->json(['total_assets' => $totalAssets]);
    }

}
