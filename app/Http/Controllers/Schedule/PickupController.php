<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PickupSchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class PickupController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $schedules = PickupSchedule::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All pickup schedules fetched successfully',
                'total' => $schedules->count(),
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $route = $schedule->route()->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule fetched successfully',
                'data' => $schedule,
                'route' => $route
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedRequest =  $request->validate([
                'route_id' => 'exists:routes,id',
                'asset_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => [Rule::exists('users', 'id')->where('role_id', 2)],
                'customer_id' => ['required', Rule::exists('users', 'id')->where('role_id', 4)],
                'pickup_date' => 'required|date',
                'status' => 'nullable|string',
                'notes' => 'nullable',
                'n_bins' => 'nullable|integer',
                'tare_weight' => 'nullable|string',
                'image' => 'nullable|mimes:jpeg,png,jpg,pdf',
                'coordinates' => 'nullable|array',
            ]);

            $schedule = PickupSchedule::create($validatedRequest);
            //TODO: Implement notification to related parties after the schedule is created
            $route = $schedule->route()->first();
            $driver = $schedule->driver()->first();
            $customer = $schedule->customer()->first();
            $asset = $schedule->asset()->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule created successfully',
                'data' => $schedule,
                'route' => $route,
                'driver' => $driver,
                'customer' => $customer,
                'asset' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $validatedRequest =  $request->validate([
                'route_id' => 'exists:routes,id',
                'asset_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => [Rule::exists('users', 'id')->where('role_id', 2)],
                'customer_id' => [Rule::exists('users', 'id')->where('role_id', 4)],
                'pickup_date' => 'date',
                'status' => 'nullable|string',
                'notes' => 'nullable',
                'n_bins' => 'integer',
                'tare_weight' => 'string',
                'image' => 'mime:jpeg,png,jpg,pdf',
                'coordinates' => 'array',
            ]);

            $schedule->update($validatedRequest);
            //TODO: Implement notification to related parties after the schedule is updated
            $route = $schedule->route()->first();
            $driver = $schedule->driver()->first();
            $customer = $schedule->customer()->first();
            $asset = $schedule->asset()->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule updated successfully',
                'data' => $schedule,
                'route' => $route,
                'driver' => $driver,
                'customer' => $customer,
                'asset' => $asset
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $schedule->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::withTrashed()->findOrFail($id);
            $schedule->restore();
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule restored successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::withTrashed()->findOrFail($id);
            $schedule->forceDelete();
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule permanently deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
