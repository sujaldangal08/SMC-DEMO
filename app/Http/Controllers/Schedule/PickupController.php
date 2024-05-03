<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\PickupRequest;
use App\Models\PickupSchedule;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PickupController extends Controller
{
    use ValidatesRoles;

    /**
     * Fetch all pickup schedules
     */
    public function index(): JsonResponse
    {
        try {
            $schedules = PickupSchedule::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All pickup schedules fetched successfully',
                'total' => $schedules->count(),
                'data' => $schedules,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a single pickup schedule
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::where('id', $id)->with('customer', 'driver', 'asset');
            $route = $schedule->route()->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule fetched successfully',
                'data' => $schedule,
                'route' => $route,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new pickup schedule
     */
    public function store(PickupRequest $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validated();
            if (isset($validatedRequest['image'])) {
                $validatedRequest = $this->imageUpload($validatedRequest);
            }
            $schedule = PickupSchedule::create($validatedRequest);
            //TODO: Implement notification to related parties after the schedule is created
            $data = $this->setRelatedData($schedule);
            $route = $data['route'];
            $driver = $data['driver'];
            $customer = $data['customer'];
            $asset = $data['asset'];

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule created successfully',
                'data' => $schedule,
                'route' => $route,
                'driver' => $driver,
                'customer' => $customer,
                'asset' => $asset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update pickup schedule details
     */
    public function update(PickupRequest $request, int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $validatedRequest = $request->validated();

            if (isset($validatedRequest['image'])) {
                $validatedRequest = $this->imageUpload($validatedRequest);
            }
            $schedule->update($validatedRequest);

            $data = $this->setRelatedData($schedule);

            $route = $data['route'];
            $driver = $data['driver'];
            $customer = $data['customer'];
            $asset = $data['asset'];

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule updated successfully',
                'data' => $schedule,
                'route' => $route,
                'driver' => $driver,
                'customer' => $customer,
                'asset' => $asset,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::withTrashed()->findOrFail($id);
            $schedule->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule restored successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::withTrashed()->findOrFail($id);
            $schedule->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pickup schedule permanently deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pickup schedule not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set related data
     *
     * @return array
     */
    private function setRelatedData(object $schedule)
    {
        $route = $schedule->route()->first();
        $driver = $schedule->driver()->first();
        $customer = $schedule->customer()->first();
        $asset = $schedule->asset()->first();

        $route = $route ? [
            'name' => $route->name,
            'description' => $route->description,
            'status' => $route->status,
            'start_date' => $route->start_date,
        ] : null;

        $driver = $driver ? [
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'image' => $driver->image,
        ] : null;

        $customer = $customer ? [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'image' => $customer->image,
        ] : null;

        $asset = $asset ? [
            'title' => $asset->title,
            'asset_type' => $asset->asset_type,
            'image' => $asset->image,
        ] : null;

        return [
            'route' => $route,
            'driver' => $driver,
            'customer' => $customer,
            'asset' => $asset,
        ];
    }

    /**
     * Upload image
     *
     * @return array
     */
    protected function imageUpload(array $validatedRequest)
    {
        $images = [];
        foreach ($validatedRequest['image'] as $image) {
            $image_name = Str::random(10) . '.' . $image->getClientOriginalExtension();
            $filePath = 'uploads/pickup/' . $image_name;

            // Check if the 'uploads/pickup' directory exists and create it if it doesn't
            if (!Storage::disk('public')->exists('uploads/pickup')) {
                Storage::disk('public')->makeDirectory('uploads/pickup');
            }
            // Save the image to a file in the public directory
            Storage::disk('public')->put($filePath, file_get_contents($image));

            $image_location = 'storage/uploads/pickup/' . $image_name;
            $images[] = $image_location;
        }
        $validatedRequest['image'] = $images;

        return $validatedRequest;
    }
}
