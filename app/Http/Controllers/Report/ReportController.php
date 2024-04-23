<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Delivery;
use App\Models\PickupSchedule;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //Retrieves the total number of deliveries based on optional filtering criteria provided through the 'status' parameter in the request.

    public function getTotalDeliveries(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled',
            ]);
            $status = $request->input('status');
            // Query the database for the total number of deliveries based on the optional status filter.
            $query = Delivery::query();
            if ($status) {
                $query->where('status', $status);
            }
            $totalDeliveries = $query->count();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => 'Data not found!'
                ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Total deliveries fetched successfully',
            'data' => ['total_deliveries' => $totalDeliveries],
            'total_deliveries' => $totalDeliveries
        ], 200);
    }

    // Retrieves the total number of pickups with optional filtering by status.

    public function getTotalPickups(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,schedule,unloading,done,completed,cancelled',
            ]);

            $status = $request->input('status');

            $query = PickupSchedule::query();
            // Query the database for the total number of pickups based on the optional status filter.

            if ($status) {
                $query->where('status', $status);
            }

            $totalPickups = $query->count();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => 'Data not found!'], 400);
        }

        return response()->json(['total_pickups' => $totalPickups], 200);
    }

    // Retrieves the total number of tickets with optional filtering by status.

    public function getTotalTickets(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled',
            ]);

            $status = $request->input('status');

            $query = Ticket::query();

            if ($status) {
                $query->where('status', $status);
                // Query the database for the total number of tickets based on the optional status filter.
            }

            $totalTickets = $query->count();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => 'Data not found!'], 404);
        }

        return response()->json(['total_tickets' => $totalTickets], 200);
    }

    // Retrieves the total number of users with optional filtering by status.

    public function getTotalUsers(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive',
            ]);

            $status = $request->input('status');
            // Query the database for the total number of users based on the optional status filter.

            $query = User::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalUsers = $query->count();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => 'Data not found!'], 404);
        }

        return response()->json(['total_users' => $totalUsers], 200);
    }

    // Retrieves the total number of assets with optional filtering by status.

    public function getTotalAssets(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,scheduled,completed,cancelled',
            ]);

            $status = $request->input('status');
            // Query the database for the total number of assets based on the optional status filter.
            $query = Asset::query();

            if ($status) {
                $query->where('status', $status);
            }

            $totalAssets = $query->count();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => 'Data not found!'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Total assets fetched successfully',
            'data' => ['total_assets' => $totalAssets],
            'total_assets' => $totalAssets], 200);
    }

    // Fetches data based on user role: customer or driver.

    public function fetchData(Request $request)
    {
        $user = $request->user();
        // Check if the user has the designated role.
        if ($user->hasRole('customer')) {
            $data = Delivery::where('id', $user->id)
                ->with(['scheduledPickups', 'tickets'])
                ->get();
        } elseif ($user->role->role === 'driver') {
            $data = Delivery::where('id', $user->id)
                ->with('scheduledPickups')
                ->get();
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized',
                'data' => null,
                'error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $data
            ], 200);

    }
}
