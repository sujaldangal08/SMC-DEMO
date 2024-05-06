<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryRequest;
use App\Models\DeliveryTrip;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class DeliveryTripController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        try {
            $trips = DeliveryTrip::all()->map(function ($trip) {
                $amount = $trip->amount_loaded;
                $weight = is_array($amount) ? array_sum($amount) : 0;
                $trip->weight_of_materials = $weight;

                return $trip;
            });

            return response()->json([
                'status' => 'successful',
                'count' => count($trips), // Add this line to return the count of the trips
                'message' => 'Delivery trips retrieved successfully.',
                'data' => [
                    'trips' => $trips,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while creating delivery trips.' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function get(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::find($id);

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip retrieved successfully.',
                'trip' => $trip,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.' . $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while retrieving delivery trip.' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function create(DeliveryRequest $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validated();
            $trip = DeliveryTrip::create($validatedRequest);

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip created successfully.',
                'trip' => $trip,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while creating delivery trip.' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(DeliveryRequest $request, int $id): JsonResponse
    {
        try {
            $validatedRequest = $request->validated();
            $trip = DeliveryTrip::find($id);
            $trip->update($validatedRequest);

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip updated successfully.',
                'trip' => $trip,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.'. $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while updating delivery trip.' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::find($id);
            $trip->delete();

            return response()->json([
                'status' => 'successful',
                'message' => 'Delivery trip deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.' . $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while deleting delivery trip.' . $e->getMessage(),
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
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.' .  $e->getMessage(),
                'data' => 'null',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while restoring delivery trip.' . $e->getMessage(),
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
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Delivery trip not found.' . $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while permanently deleting delivery trip.' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
