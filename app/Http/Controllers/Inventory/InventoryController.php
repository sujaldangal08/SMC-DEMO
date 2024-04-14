<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Sku;
use Illuminate\Validation\ValidationException;


class InventoryController extends Controller
{

    // Inventory
    // Method to get inventory details
    public function inventory() :\Illuminate\Http\JsonResponse
    {
        // Define SKU ID
        $skuId = 1;

        // Get all SKU
        $sku = sku::all();


        // Get inventory details for the given SKU ID
        $inventory = Inventory::where('SKU_id', $skuId)->get();

        // Get warehouse details for the given SKU ID
        $warehouse = Warehouse::where('SKU_id', $skuId)->get();

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Inventories retrieved successfully',
            'SKU' => $sku,
            'Inventory' =>  $inventory,
            'Warehouse' => $warehouse
        ]);
    }

    // Method to insert inventory
    public function createInventory(Request $request) :\Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'SKU_id' => 'required|integer',
            'name' => 'required|string',
            'thumbnail_image' => 'required|string',
            'description' => 'required|string',
            'material_type' => 'required|string',
            'stock' => 'required|integer',
            'cost_price' => 'required|numeric',
            'manufacturing' => 'required|string',
            'supplier' => 'required|string',
            'serial_number' => 'required|string'
        ]);

        // Create a new inventory with the validated data
        $inventory = Inventory::create($data);

        // Return a JSON response with the status, message, and the created inventory
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory inserted successfully',
            'data' => $inventory
        ]);
    }

    // Method to update inventory
    public function updateInventory(Request $request, $id) :\Illuminate\Http\JsonResponse
    {
        $inventory = Inventory::find($id);

      // Validate the request data
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'thumbnail_image' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'material_type' => 'sometimes|required|string',
            'stock' => 'sometimes|required|integer',
            'cost_price' => 'sometimes|required|numeric',
            'manufacturing' => 'sometimes|required|string',
            'supplier' => 'sometimes|required|string',
            'serial_number' => 'sometimes|required|string|unique:inventories,serial_number,' . $id,
            'SKU_id' => 'sometimes|required|integer',
        ]);

        // Find the inventory by its ID
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found',
            ], 404);
        }

        // Update the inventory with the new data
        $inventory->update($data);

        // Return a JSON response with the status, message, and the updated inventory
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory updated successfully',
            'data' => $inventory
        ]);
    }

    // Method to delete inventory
    public function deleteInventory($id) :\Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found',
            ], 404);
        }

        // Delete the inventory
        $inventory->delete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory deleted successfully',
        ]);
    }

    // Method to restore inventory
    public function restoreInventory($id) :\Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::withTrashed()->find($id);

        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found',
            ], 404);
        }

        // Restore the inventory
        $inventory->restore();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory restored successfully',
        ]);
    }

    // Method to    delete inventory permanently
    public function permanentDeleteInventory($id) :\Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::withTrashed()->find($id);

        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found',
            ], 404);
        }

        // Delete the inventory permanently
        $inventory->forceDelete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory permanently deleted',
        ]);
    }

}
