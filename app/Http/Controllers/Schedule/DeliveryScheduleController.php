<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliverySchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Traits\ValidatesRoles;
use Illuminate\Validation\Rule;

class DeliveryScheduleController extends Controller
{
    use ValidatesRoles;
    public function index(): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule retrieved successfully',
                'schedule' => $schedule
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule retrieved successfully',
                'schedule' => $schedule
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'customer_id' => ['required', $this->roleRule('customer')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'coordinates' => 'required|array',
                'materials' => 'required|array',
                'amount' => ['required', 'array', 'size:' . count($request->input('materials'))],
                'n_trips' => 'required|integer',
                'interval' => 'required|integer',
                'start_date' => 'required|date',
                'status' => 'required|in:pending,completed,cancelled',
                'delivery_notes' => 'nullable|string',
                'meta' => 'nullable|array'
            ]);
            // Calculate the end date based on the number of trips and interval
            $totalDays = $validatedData['n_trips'] * $validatedData['interval'];
            $validatedData['end_date'] = date('Y-m-d', strtotime($validatedData['start_date'] . ' + ' . $totalDays . ' days'));

            $schedule = DeliverySchedule::create($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule created successfully',
                'schedule' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'customer_id' => ['nullable', $this->roleRule('customer')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'coordinates' => 'nullable|array',
                'materials' => 'nullable|array',
                'amount' => ['nullable', 'array', 'size:' . count($request->input('materials'))],
                'n_trips' => 'nullable|integer',
                'interval' => 'nullable|integer',
                'start_date' => 'nullable|date',
                'status' => 'nullable|in:pending,completed,cancelled',
                'delivery_notes' => 'nullable|string',
                'meta' => 'nullable|array'
            ]);
            
            if (isset($validatedData['n_trips']) && isset($validatedData['interval'])) {
                // Calculate the end date based on the number of trips and interval
                $totalDays = $validatedData['n_trips'] * $validatedData['interval'];
                $validatedData['end_date'] = date('Y-m-d', strtotime($validatedData['start_date'] . ' + ' . $totalDays . ' days'));
            }

            $schedule = DeliverySchedule::findOrFail($id);
            $schedule->update($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule updated successfully',
                'schedule' => $schedule
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::findOrFail($id);
            $schedule->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::withTrashed()->findOrFail($id);
            $schedule->restore();
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule restored successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while restoring the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::withTrashed()->findOrFail($id);
            $schedule->forceDelete();
            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule permanently deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while permanently deleting the delivery schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
