<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\PickupSchedule;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PickupController extends Controller
{
    use ValidatesRoles;

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

    public function show(int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
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

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'route_id' => ['nullable', 'exists:routes,id'],
                'asset_id' => ['nullable', Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'customer_id' => ['nullable', $this->roleRule('customer')],
                'pickup_date' => 'required|date',
                'status' => 'nullable|in:pending,active,inactive,done,unloading,full,schedule',
                'notes' => 'nullable',
                'materials' => 'nullable|array',
                'amount' => ['nullable', 'array', 'size:'.count($request->input('materials'))],
                'weighing_type' => ['nullable', 'array', 'in:bridge,pallet', 'size:'.count($request->input('materials'))],
                'n_bins' => 'nullable|integer',
                'tare_weight' => ['nullable'],
                'image' => 'nullable|mimes:jpeg,png,jpg,pdf',
                'coordinates' => 'nullable|array',
            ]);
            $image = [];
            foreach ($validatedRequest['image'] as $image) {
                $image = $request->file('image');
                $image_name = Str::random(10).'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/profile/');
                $image->move($destinationPath, $image_name);
                $image_location = 'uploads/profile/'.$image_name;
                $image[] = $image_location;
            }

            $validatedRequest['image'] = $image;

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
                'asset' => $asset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = PickupSchedule::findOrFail($id);
            $validatedRequest = $request->validate([
                'route_id' => 'exists:routes,id',
                'asset_id' => ['nullable', Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
                'driver_id' => ['nullable', $this->roleRule('driver')],
                'customer_id' => ['required', $this->roleRule('customer')],
                'pickup_date' => 'nullable|date',
                'status' => 'nullable|in:pending,active,inactive,done,unloading,full,schedule',
                'notes' => 'nullable',
                'materials' => 'nullable|array',
                'amount' => ['nullable', 'array', 'size:'.count($request->input('materials'))],
                'weighing_type' => ['nullable', 'array', 'in:bridge,pallet', 'size:'.count($request->input('materials'))],
                'n_bins' => 'nullable|integer',
                'tare_weight' => ['nullable', 'array', 'size:'.(is_array($request->input('n_bins')) ? count($request->input('n_bins')) : 0)],
                'image' => 'nullable|mimes:jpeg,png,jpg,pdf',
                'coordinates' => 'nullable|array',
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
}
