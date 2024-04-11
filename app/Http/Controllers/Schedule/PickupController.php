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
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule fetched successfully',
                'data' => $schedule
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
                'asset_id' => ['required', Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => ['required', Rule::exists('users', 'id')->where('role_id', 2)],
                'customer_id' => ['required', Rule::exists('users', 'id')->where('role_id', 4)],
                'pickup_date' => 'required|date',
                'status' => 'nullable|string',
                'notes' => 'nullable'
            ]);

            $schedule = PickupSchedule::create($validatedRequest);
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule created successfully',
                'data' => $schedule
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
                'asset_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => [Rule::exists('users', 'id')->where('role_id', 2)],
                'customer_id' => [Rule::exists('users', 'id')->where('role_id', 4)],
                'pickup_date' => 'date',
                'status' => 'nullable|string',
                'notes' => 'nullable'
            ]);

            $schedule->update($validatedRequest);
            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule updated successfully',
                'data' => $schedule
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
