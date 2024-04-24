<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        try {
            $routes = Route::with(['schedule.customer'])->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All routes fetched successfully',
                'total' => $routes->count(),
                'routes' => $routes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $route = Route::findOrFail($id);
            $pickup = optional($route->schedule()->get())->toArray();

            return response()->json([
                'status' => 'success',
                'message' => 'Route fetched successfully',
                'data' => $route,
                'pickup' => $pickup,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Route not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'driver_id' => ['nullable', 'exists:users,id', $this->roleRule('driver')],
                'asset_id' => ['nullable', 'exists:assets,id'],
                'status' => 'required|in:active,inactive,pending,full',
                'start_date' => 'required|date',
            ]);

            $route = Route::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Route created successfully',
                'data' => $route,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $route = Route::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'string',
                'description' => 'string',
                'status' => 'in:active,inactive,done,pending,full',
                'driver_id' => ['nullable', $this->roleRule('driver')], // 'driver_id' => 'exists:users,id
                'asset_id' => 'nullable|exists:assets,id',
                'start_date' => 'date',
            ], [
                'driver_id.exists' => 'The selected driver is invalid',
            ]);

            $route->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => $route,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Route not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $route = Route::findOrFail($id);
            $route->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Route deleted successfully',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Route not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $route = Route::withTrashed()->findOrFail($id);
            $route->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Route restored successfully',
                'data' => $route,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Route not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $route = Route::withTrashed()->findOrFail($id);
            $route->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Route permanently deleted successfully',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Route not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
