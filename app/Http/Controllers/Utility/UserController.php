<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function RetreiveUser()
    {
        try {
            $users = User::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All users fetched successfully',
                'total' => $users->count(),
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    public function RetreiveDriver()
    {
        try {
            $drivers = User::whereHas('role', function ($query) {
                $query->where('role', 'driver');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All drivers fetched successfully',
                'total' => $drivers->count(),
                'data' => $drivers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function RetreiveManager()
    {
        try {
            $managers = User::whereHas('role', function ($query) {
                $query->where('role', 'manager');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All managers fetched successfully',
                'total' => $managers->count(),
                'data' => $managers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function RetreiveStaff()
    {
        try {
            $staffs = User::whereHas('role', function ($query) {
                $query->where('role', 'staff');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All staffs fetched successfully',
                'total' => $staffs->count(),
                'data' => $staffs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function RetreiveCustomer()
    {
        try {
            $customers = User::whereHas('role', function ($query) {
                $query->where('role', 'customer');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All customers fetched successfully',
                'total' => $customers->count(),
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function RetreiveAdmin()
    {
        try {
            $admins = User::whereHas('role', function ($query) {
                $query->where('role', 'admin');
            })->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All admins fetched successfully',
                'total' => $admins->count(),
                'data' => $admins
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
