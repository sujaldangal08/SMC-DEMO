<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DriverController extends Controller
{
    public function driverRoute(): JsonResponse
    {
        try {
            $driver_id = request()->user()->id;
            $route = Route::where('driver_id', $driver_id)->with(['schedule.customer'])->get()->map(function ($route) {
                $route->customer_names = $route->schedule->map(function ($schedule) {
                    return $schedule->customer->name;
                });
                $route->total_materials = $route->schedule->map(function ($schedule) {
                    $amount = $schedule->amount;
                    return is_array($amount) ? array_sum($amount) : 0;
                });
                $route->makeHidden('schedule');
                return [
                    'id' => $route->id,
                    'name' => $route->name,
                    'driver_id' => $route->driver_id,
                    'asset_id' => $route->asset_id,
                    'description' => $route->description,
                    'status' => $route->status,
                    'start_date' => $route->start_date,
                    'deleted_at' => $route->deleted_at,
                    'created_at' => $route->created_at,
                    'updated_at' => $route->updated_at,
                    'customer_names' => $route->customer_names,
                    'amount' => $route->total_materials,
                ];
            });
            // dd($route);
            return response()->json([
                'status' => 'success',
                'message' => 'All routes fetched successfully',
                'total' => $route->count(),
                'data' => $route,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function detailRoute(int $id): JsonResponse
    {
        try {
            $route = Route::findOrFail($id)->where('driver_id', request()->user()->id)->first();
            $pickups = $route->schedule()->with(['customer' => function ($query) {
                $query->select('id', 'name', 'phone_number');
            }])->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Route fetched successfully',
                'data' => [
                    'route' => [
                        'id' => $route->id,
                        'name' => $route->name,
                    ],
                    'pickups' => $pickups,
                ]
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
