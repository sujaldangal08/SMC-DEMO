<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\PickSchedule;
use Illuminate\Http\Request;
use App\Models\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $routes = Route::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All routes fetched successfully',
                'total' => $routes->count(),
                'data' => $routes
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
            $route = Route::findOrFail($id);
            $pickup = optional($route->schedule()->get())->toArray();
            return response()->json([
                'status' => 'success',
                'message' => 'Route fetched successfully',
                'data' => $route,
                'pickup' => $pickup
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found'
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
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'status' => 'required|in:active,inactive,pending,full'
            ]);

            $route = Route::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Route created successfully',
                'data' => $route
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
            $route = Route::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'string',
                'description' => 'string',
                'status' => 'in:active,inactive,done,pending,full'
            ]);

            $route->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Route updated successfully',
                'data' => $route
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $route = Route::findOrFail($id);
            $route->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Route deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found'
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
            $route = Route::withTrashed()->findOrFail($id);
            $route->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Route restored successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found'
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
            $route = Route::withTrashed()->findOrFail($id);
            $route->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Route permanently deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
