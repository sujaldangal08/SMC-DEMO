<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    //
    // Method to get warehouse details
    public function warehouse(): \Illuminate\Http\JsonResponse
    {
        $warehouseData = Warehouse::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse retrieved successfully',
            'data' => $warehouseData,
        ], 200);
    }

    public function createWarehouse(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'location' => 'required|string',
                'SKU_id' => 'required|array',
            ]);

            // Create a new warehouse with the validated data
            $warehouse = Warehouse::create($data);

            // Return a JSON response with the status, message, and the created warehouse
            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse created successfully',
                'data' => $warehouse,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create warehouse: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateWarehouse(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'location' => 'sometimes|required|string',
                'SKU_id' => 'sometimes|required|array',
            ]);

            // Find the warehouse by its ID
            $warehouse = Warehouse::find($id);

            if (! $warehouse) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Warehouse not found',
                    'data' => null
                ], 404);
            }

            // Update the warehouse with the new data
            $warehouse->update($data);

            // Return a JSON response with the status, message, and the updated warehouse
            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse updated successfully',
                'data' => $warehouse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update warehouse: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Method to delete warehouse
    public function deleteWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json([
                'message' => 'Warehouse not found',
            ], 404);
        }

        // Delete the warehouse
        $warehouse->delete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse deleted successfully',
        ], 200);
    }

    // Method to restore warehouse
    public function restoreWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::withTrashed()->find($id);

        if (! $warehouse) {
            return response()->json([
                'message' => 'Warehouse not found',
            ], 404);
        }

        // Restore the warehouse
        $warehouse->restore();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse restored successfully',
        ], 200);
    }

    // Method to permanently delete warehouse
    public function permanentDeleteWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::withTrashed()->find($id);

        if (! $warehouse) {
            return response()->json([
                'message' => 'Warehouse not found',
            ], 404);
        }

        // Permanently delete the warehouse
        $warehouse->forceDelete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse permanently deleted',
        ], 200);
    }
}
