<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function RetrieveSingleUser()
    {
        try {
            // Get the currently authenticated user
            $user = auth()->user();

            // If there is no authenticated user, return an error response
            if (! $user) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'No authenticated user',
                    'data' => null,
                ], 401);
            }

            // Return the authenticated user's data
            return response()->json([
                'status' => 'success',
                'message' => 'User fetched successfully.',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveUsers()
    {
        try {
            $users = User::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All users fetched successfully',
                'total' => $users->count(),
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveDriver()
    {
        try {
            $drivers = User::whereHas('role', function ($query) {
                $query->where('role', 'driver');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All drivers fetched successfully',
                'total' => $drivers->count(),
                'data' => $drivers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveManager()
    {
        try {
            $managers = User::whereHas('role', function ($query) {
                $query->where('role', 'manager');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All managers fetched successfully',
                'total' => $managers->count(),
                'data' => $managers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveStaff()
    {
        try {
            $staffs = User::whereHas('role', function ($query) {
                $query->where('role', 'staff');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All staffs fetched successfully',
                'total' => $staffs->count(),
                'data' => $staffs,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveCustomer()
    {
        try {
            $customers = User::whereHas('role', function ($query) {
                $query->where('role', 'customer');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All customers fetched successfully',
                'total' => $customers->count(),
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function RetrieveAdmin()
    {
        try {
            $admins = User::whereHas('role', function ($query) {
                $query->where('role', 'admin');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All admins fetched successfully',
                'total' => $admins->count(),
                'data' => $admins,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
