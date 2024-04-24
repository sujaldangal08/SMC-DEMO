<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTrip;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryTripController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        try {
            $trips = DeliveryTrip::all();

            return response()->json([
                'status' => 'success',
                'count' => count($trips), // Add this line to return the count of the trips
                'message' => 'Delivery trips retrieved successfully.',
                'data' => $trips,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating delivery trips.'], 500);
        }
    }

    public function get(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::find($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip retrieved successfully.',
                'data' => $trip,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while retrieving delivery trip.',
                'data' => null,
            ], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'vehicle_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'materials' => 'required|array',
                'amount_loaded' => 'required|array',
                'status' => 'required|in:pending,in_progress,completed',
                'trip_number' => 'required|string',
            ]);
            $trip = DeliveryTrip::create($validatedRequest);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip created successfully.',
                'data' => $trip,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while creating delivery trip.',
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'vehicle_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'materials' => 'required|array',
                'amount_loaded' => 'required|array',
            ]);
            $trip = DeliveryTrip::find($id);
            $trip->update($validatedRequest);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip updated successfully.',
                'trip' => $trip,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Delivery trip not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating delivery trip.'], 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::find($id);
            $trip->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip deleted successfully.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while deleting delivery trip.',
                'data' => null,
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::withTrashed()->find($id);
            $trip->restore();

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip restored successfully.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while restoring delivery trip.',
                'data' => null,
            ], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::withTrashed()->find($id);
            $trip->forceDelete();

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip permanently deleted successfully.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while permanently deleting delivery trip.',
                'data' => null,
            ], 500);
        }
    }
}
