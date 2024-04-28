<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Get all maintenance
     *
     * @return JsonResponse
     */
    public function getAllMaintenance(): JsonResponse
    {
        try {
            $maintenances = Maintenance::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All maintenance fetched successfully',
                'data' => $maintenances,
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
     * Get a single maintenance
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getOneMaintenance(int $id): JsonResponse
    {
        try {
            $maintenances = Maintenance::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance fetched successfully',
                'data' => $maintenances,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Maintenance not found',
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

    /**
     * Create a new maintenance
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createMaintenance(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'maintenance_type' => 'required|string',
                'contact_meta' => 'required|string',
                'service_date' => 'required|date',
            ]);

            $maintenance = new Maintenance();
            $maintenance->asset_id = $request->asset_id;
            $maintenance->maintenance_type = $request->maintenance_type;
            $maintenance->contact_meta = json_decode($request->contact_meta, true);
            $maintenance->service_date = $request->service_date;
            $maintenance->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance created successfully',
                'data' => $maintenance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update a maintenance
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateMaintenance(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'asset_id' => 'exists:assets,id',
                'maintenance_type' => 'string',
                'contact_meta' => 'array',
                'service_date' => 'date',
            ], [
                'contact_meta.array' => 'The contact meta must be an array',
            ]);

            $maintenance = Maintenance::findOrFail($id);

            $meta = $maintenance->contact_meta;  // Retrieve the 'meta' array
            $meta = array_merge($meta, $request->get('contact_meta', []));
            $meta = array_filter($meta, 'strlen');  // Remove keys with null or empty values

            $maintenance->asset_id = $request->asset_id ?? $maintenance->asset_id;
            $maintenance->maintenance_type = $request->maintenance_type ?? $maintenance->maintenance_type;
            $maintenance->contact_meta = $meta ?? $maintenance->contact_meta;
            $maintenance->service_date = $request->service_date ?? $maintenance->service_date;
            $maintenance->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance updated successfully',
                'data' => $maintenance,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Maintenance not found',
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

    /**
     * Delete a maintenance
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteMaintenance(int $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance deleted successfully',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Maintenance not found',
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

    /**
     * Restore a maintenance
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restoreMaintenance(int $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::withTrashed()->findOrFail($id);
            $maintenance->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance restored successfully',
                'data' => $maintenance,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Maintenance not found',
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

    /**
     * Permanently delete a maintenance
     *
     * @param int $id
     * @return JsonResponse
     */
    public function permanentDeleteMaintenance(int $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::withTrashed()->findOrFail($id);
            $maintenance->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance permanently deleted successfully',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Maintenance not found',
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
