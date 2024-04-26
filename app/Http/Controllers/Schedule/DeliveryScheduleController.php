<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use App\Traits\ValidatesRoles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryScheduleController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        try {
            // Retrieve all delivery schedules
            $schedules = DeliverySchedule::all()->map(function ($schedule) {
                $amount = $schedule->amount;
                $totalAmount = is_array($amount) ? array_sum($amount) : 0;
                $schedule->total_amount = $totalAmount;
                return $schedule;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedules retrieved successfully',
                'data' => [
                    'schedule' => $schedules
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the delivery schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            // Retrieve a single delivery schedule
            $schedule = DeliverySchedule::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule retrieved successfully',
                'schedule' => $schedule,
            ], 200);
        } catch (ModelNotFoundException $e) { // More specific exception
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'customer_id' => ['required', $this->roleRule('customer')], //Using the roleRule method from the ValidatesRoles trait
                'driver_id' => ['nullable', $this->roleRule('driver')], // Using the roleRule method from the ValidatesRoles trait
                'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'coordinates' => 'required|array',
                'locale' => 'required|in:domestic,international', // Validate the locale based on the given options
                'delivery_date' => 'required_if:locale,international|array', // Validate the delivery date as an array only if the locale is international
                'materials' => 'required|array',
                'amount' => ['required', 'array', 'size:' . count($request->input('materials'))], // Validate the amount array based on the number of materials
                'n_trips' => 'required_if:locale,domestic', // Validate the number of trips based on the number of delivery  dates only if the locale is international
                'interval' => 'required_if:locale,domestic|integer',
                'start_date' => 'required|date',
                'status' => 'required|in:pending,completed,cancelled', // Validate the status based on the given options
                'delivery_notes' => 'nullable|string',
                'meta' => 'nullable|array',
            ]);

            //Calculate the delivery date if there is no delivery date in the request object needed for CRON job
            if (!$request->has('delivery_date')) {
                // Calculate the delivery dates
                $deliveryDates = [];
                // Loop through the number of trips
                for ($i = 0; $i < $request->input('n_trips'); $i++) {
                    // Calculate the delivery date based on the interval
                    $deliveryDate = date('Y-m-d', strtotime($request->input('start_date') . ' + ' . ($i * $request->input('interval')) . ' days'));
                    $deliveryDates[] = $deliveryDate;
                }
                // Add the calculated delivery dates to the validated data
                $validatedData['delivery_date'] = $deliveryDates;
            }

            //Calculate the number of trips
            if (!$request->has('n_trips')) {
                $validatedData['n_trips'] = count($validatedData['delivery_date']);
            }

            //Calculate the end date
            $endDate = end($validatedData['delivery_date']);
            $validatedData['end_date'] = $endDate;

            $schedule = DeliverySchedule::create($validatedData); // Create a new delivery schedule based on the validated data

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule created successfully',
                'schedule' => $schedule,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validate the request data according to the database schema and logic
            $validatedData = $request->validate([
                'customer_id' => ['required', $this->roleRule('customer')], //Using the roleRule method from the ValidatesRoles trait
                'driver_id' => ['nullable', $this->roleRule('driver')], // Using the roleRule method from the ValidatesRoles trait
                'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'coordinates' => 'required|array',
                'locale' => 'required|in:domestic,international', // Validate the locale based on the given options
                'delivery_date' => 'required|date|array', // Validate the delivery date as an array
                'materials' => 'required|array',
                'delivery_date.*' => 'required_if:locale,international|date|array', // Validate each delivery date if the locale is international
                'amount' => ['required', 'array', 'size:' . count($request->input('materials'))], // Validate the amount array based on the number of materials
                'n_trips' => ['required|integer', 'size:' . count($request->input('delivery_date')) . '|required_if:locale,international'], // Validate the number of trips based on the number of delivery dates only if the locale is international
                'interval' => 'required_if:locale,domestic|integer',
                'start_date' => 'required|date',
                'status' => 'required|in:pending,completed,cancelled', // Validate the status based on the given options
                'delivery_notes' => 'nullable|string',
                'meta' => 'nullable|array',
            ]);

            if (!$request->has('delivery_date')) {
                // Calculate the delivery dates
                $deliveryDates = [];
                // Loop through the number of trips
                for ($i = 0; $i < $request->input('n_trips'); $i++) {
                    // Calculate the delivery date based on the interval
                    $deliveryDate = date('Y-m-d', strtotime($request->input('start_date') . ' + ' . ($i * $request->input('interval')) . ' days'));
                    $deliveryDates[] = $deliveryDate;
                }
                // Add the calculated delivery dates to the validated data
                $validatedData['delivery_date'] = $deliveryDates;
            }

            // Find the delivery schedule by ID and update the schedule
            $schedule = DeliverySchedule::findOrFail($id);
            $schedule->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule updated successfully',
                'schedule' => $schedule,
            ], 200);
        } catch (ModelNotFoundException $e) { // More specific exception for model not found
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Method to delete a delivery schedule
    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Method to restore a soft-deleted delivery schedule
    public function restore(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::withTrashed()->findOrFail($id);
            $schedule->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule restored successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while restoring the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Method to permanently delete a delivery schedule from the database
    // This action is irreversible
    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $schedule = DeliverySchedule::withTrashed()->findOrFail($id);
            $schedule->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery schedule permanently deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while permanently deleting the delivery schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deliveryDate()
    {
        // Start date
        $startDate = Carbon::createFromFormat('Y/m/d', '2024/04/01');

        // Number of trips
        $n_trips = 5;

        // Interval between trips in days
        $interval = 3;

        // Array to hold the delivery dates
        $deliveryDates = [];

        for ($i = 0; $i < $n_trips; $i++) {
            // Add the interval * number of trips to the start date
            $deliveryDate = $startDate->copy()->addDays(($interval * $i) + 1);
            // Add the delivery date to the array
            $deliveryDates[] = $deliveryDate->format('Y/m/d');
        }

        // Now $deliveryDates contains the dates on which the deliveries will be made
        print_r($deliveryDates);

        // The end date is the last element in the deliveryDates array
        $endDate = end($deliveryDates);

        echo 'End date: ' . $endDate;
    }
}
