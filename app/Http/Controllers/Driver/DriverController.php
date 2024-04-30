<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryRequest;
use App\Http\Requests\DriverPickup;
use App\Models\DeliveryTrip;
use App\Models\PickupSchedule;
use App\Models\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    /**
     * Get the driver's dashboard
     *
     * @return json dashboard
     *
     * @throws \Exception
     */
    public function driverDashboard(): JsonResponse
    {
        try {
            $dashboard = [
                'routes' => Route::where('driver_id', request()->user()->id)->where('status', 'active')->with('schedule')->get(),
                'delivery' => DeliveryTrip::where('driver_id', request()->user()->id)->where('status', 'in_progress')->get(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard fetched successfully',
                'data' => $dashboard,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get the driver's route with the schedule and customer relationship
     *
     * @throws \Exception
     */
    public function driverRoute(): JsonResponse
    {
        try {
            $driver_id = request()->user()->id;
            $route = Route::where('driver_id', $driver_id)->with(['schedule.customer'])->get()->map(function ($route) {
                // Get the customer names from the schedule relationship with the customer model
                $route->customer_names = $route->schedule->map(function ($schedule) {
                    return $schedule->customer->name;
                });
                //Calculate the total amount of materials in the route with the model relation through the schedule
                $route->total_materials = $route->schedule->map(function ($schedule) {
                    $amount = $schedule->amount;

                    return is_array($amount) ? array_sum($amount) : 0;
                });
                // Hide the schedule attribute other wise the object will be too large
                unset($route->schedule);

                return [
                    // Select only the required columns from the route doing this because directly returning teh route was not working
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

    /**
     * Get the driver's route with the schedule and customer relationship
     *
     * @throws \Exception
     */
    public function detailRoute(int $id): JsonResponse
    {
        try {
            // Find the route associated with the driver
            $route = Route::findOrFail($id)->where('driver_id', request()->user()->id)->first();
            $pickups = $route->schedule()->with(['customer' => function ($query) { // Eager load customer relationship
                // Select only the required columns
                $query->select('id', 'name', 'phone_number');
            }])->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Route fetched successfully',
                'data' => [
                    'route' => [
                        // Select only the required columns
                        'id' => $route->id,
                        'name' => $route->name,
                    ],
                    'pickups' => $pickups,
                ],

            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the route status
     *
     * @throws \Exception
     */
    public function updateRoute(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'status' => 'required|string|in:completed,in_progress,pending,full',
            ]);

            $route = Route::findOrFail($id)->where('driver_id', request()->user()->id)->first();

            $route->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get the schedule data
     *
     * @throws \Exception
     */
    public function detailSchedule(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id)->where('driver_id', request()->user()->id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule fetched successfully',
                'data' => [
                    'schedule' => $schedule,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * Update the schedule status
     *
     * @throws \Exception
     */
    public function updateSchedule(DriverPickup $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $schedule = PickupSchedule::findOrFail($id)->where('driver_id', request()->user()->id)->first();

            if (isset($validatedRequest['image'])) {
                $validatedData = $this->imageUpload($validatedData);
            }

            $schedule->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule updated successfully',
                'data' => [
                    'schedule' => $schedule,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get the driver's delivery trips
     *
     * @throws \Exception
     */
    public function deliveryTrips(): JsonResponse
    {
        try {
            $trip = DeliveryTrip::where('driver_id', request()->user()->id)->get()->map(function ($trip) {
                $trip->weight_of_materials = is_array($trip->amount_loaded) ? array_sum($trip->amount_loaded) : 0;
                $trip->coordinate = $trip->schedule->coordinates;
                unset($trip->schedule);

                return $trip;
            });

            if (! $trip) {
                throw new ModelNotFoundException('Delivery trips not found');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trips retrieved successfully.',
                'data' => $trip,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get the driver's delivery trips
     *
     * @throws \Exception
     */
    public function detailDeliveryTrip(int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::where('id', $id)->where('driver_id', request()->user()->id)->first();
            if (! $trip) {
                throw new ModelNotFoundException('Delivery trip not found');
            }
            $trip->customer = [
                'name' => $trip->schedule->customer->name,
                'phone_number' => $trip->schedule->customer->phone_number,
                'address' => $trip->schedule->customer->address,
            ];
            $weight = is_array($trip->amount_loaded) ? array_sum($trip->amount_loaded) : 0;
            $trip->weight_of_materials = $weight;
            $trip->coordinate = $trip->schedule->coordinates;
            $trip->emergency_contact = [
                'name' => 'John Doe',
                'phone_number' => '08012345678',
                'message' => 'This is an emergency message. Please call me back.',
            ];
            unset($trip->schedule);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip retrieved successfully.',
                'data' => $trip,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the delivery trip status
     *
     * @throws \Exception
     */
    public function updateDeliveryTrip(DeliveryRequest $request, int $id): JsonResponse
    {
        try {
            $trip = DeliveryTrip::where('id', $id)->where('driver_id', request()->user()->id)->first();

            if (! $trip) {
                throw new ModelNotFoundException('Delivery trip not found');
            }

            $validatedData = $request->validated();

            // Upload image
            if ($validatedData['status'] === 'completed' && (! isset($validatedData['attachment']) || $trip['attachment'] === null)) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Image is required when status is done.',
                    'data' => null,
                ], 422);
            }
            $images = [];
            // Check if request has image
            if (isset($validatedData['attachment']) && $validatedData['attachment'] !== null) {
                $images = $this->imageUpload($validatedData);
                $validatedData['attachment'] = $images;
            }
            $trip->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery trip updated successfully',
                'data' => [
                    'trip' => $trip,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Upload image
     *
     *
     * @return array
     */
    protected function imageUpload(array $validatedRequest)
    {
        $images = [];
        foreach ($validatedRequest['image'] as $image) {
            $image_name = Str::random(10).'.'.$image->getClientOriginalExtension();
            $filePath = 'uploads/pickup/'.$image_name;

            // Check if the 'uploads/pickup' directory exists and create it if it doesn't
            if (! Storage::disk('public')->exists('uploads/pickup')) {
                Storage::disk('public')->makeDirectory('uploads/pickup');
            }
            // Save the image to a file in the public directory
            Storage::disk('public')->put($filePath, file_get_contents($image));

            $image_location = 'storage/uploads/pickup/'.$image_name;
            $images[] = $image_location;
        }
        $validatedRequest['image'] = $images;

        return $validatedRequest;
    }
}
