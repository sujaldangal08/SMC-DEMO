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

    // Method to insert warehouse
    public function createWarehouse(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'location' => 'required|max:255',
            'sku_id' => 'required|max:255|',
        ]);

        $warehouseLocation = $validatedData['location'];
        $skuString = $validatedData['sku_id'];

        // Find the SKU by its string
        $sku = Sku::where('SKU', $skuString)->first();

        if (!$sku) {
                return response()->json([
                'status' => 'failure',
                'message' => 'SKU not found',
                'data' => null
            ], 404);
        }

        // Check if the warehouse already exists
        $warehouse = Warehouse::where('location', $warehouseLocation)->first();

        if ($warehouse) {
            // Warehouse exists, add the SKU ID to the existing SKU IDs
            $existingSkuIds = $warehouse->SKU_id;

            if (! in_array($sku->id, $existingSkuIds)) {
                $existingSkuIds[] = $sku->id;
            }

            $warehouse->SKU_id = $existingSkuIds;
            $warehouse->save();
        } else {
            // Warehouse doesn't exist, create a new one with the SKU ID
            $warehouse = Warehouse::create([
                'location' => $warehouseLocation,
                'SKU_id' => [$sku->id],
            ]);
        }

        // Return a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse inserted successfully',
            'data' => $warehouse,
        ], 201);
    }

    // Method to update warehouse
    public function updateWarehouse(Request $request, $id): \Illuminate\Http\JsonResponse
    {
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
    }

    // Method to delete warehouse
    public function deleteWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Warehouse not found',
                'data' => null
            ], 404);
        }

        // Delete the warehouse
        $warehouse->delete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse deleted successfully',
            'data' => null
        ], 200);
    }

    // Method to restore warehouse
    public function restoreWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::withTrashed()->find($id);

        if (! $warehouse) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Warehouse not found',
                'data' => null
            ], 404);
        }

        // Restore the warehouse
        $warehouse->restore();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse restored successfully',
            'data' => $warehouse
        ], 200);
    }

    // Method to permanently delete warehouse
    public function permanentDeleteWarehouse($id): \Illuminate\Http\JsonResponse
    {
        // Find the warehouse by its ID
        $warehouse = Warehouse::withTrashed()->find($id);

        if (! $warehouse) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Warehouse not found',
                'data' => null
            ], 404);
        }

        // Permanently delete the warehouse
        $warehouse->forceDelete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse permanently deleted',
            'data' => null
        ], 200);
    }
}
