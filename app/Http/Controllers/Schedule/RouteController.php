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
            $query = Route::query();

            $filters = ['status', 'branch_id', 'driver_id', 'asset_id', 'start_date',];
            foreach ($filters as $filter) {
                if (request()->has($filter)) {
                    $query->where($filter, request($filter));
                }
            }
            $routes = $query->with([
                'driver:id,name,image',
                'asset:id,title,rego_number,image',
                'schedule:id,route_id',
            ])->paginate(request('paginate', 10));

            return response()->json([
                'status' => 'success',
                'message' => 'All routes fetched successfully',
                'routes' => $routes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch all routes ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {

            $route = Route::findOrFail($id);
            $route = $route->load([
                'driver:id,name,image',
                'asset:id,title,rego_number,image',
                'schedule.customer:id,name,phone_number,image',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Route fetched successfully',
                'data' => $route,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch route' . $e->getMessage(),
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
                'status' => 'error',
                'message' => 'Failed to create route ' . $e->getMessage(),
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
                'status' => 'error',
                'message' => 'Route not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update route' . $e->getMessage(),
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
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete route' . $e->getMessage(),
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
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore route ' . $e->getMessage(),
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
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to permanently delete route' . $e->getMessage(),
            ], 500);
        }
    }
}
