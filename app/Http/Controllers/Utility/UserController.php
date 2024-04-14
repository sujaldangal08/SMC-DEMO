<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function RetreiveDriver()
    {
        try {
            $drivers = User::hasRole( 'driver')->get();

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
}
