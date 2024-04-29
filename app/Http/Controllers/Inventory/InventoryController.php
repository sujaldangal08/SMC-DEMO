<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Get all inventory
     */
    public function inventory(): \Illuminate\Http\JsonResponse
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
            'Inventory' => $inventory,
            'Warehouse' => $warehouse,
        ], 200);
    }

    /**
     * Get a single inventory
     *
     * @param  $id
     */
    public function createInventory(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'SKU_id' => 'required|integer',
                'name' => 'required|string',
                'thumbnail_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'description' => 'required|string',
                'material_type' => 'required|string',
                'stock' => 'required|integer',
                'cost_price' => 'required|numeric',
                'manufacturing' => 'required|string',
                'supplier' => 'required|string',
                'serial_number' => 'required|string',
            ]);

            // Handle the image upload
            if ($request->hasFile('thumbnail_image')) {
                $image = $request->file('thumbnail_image');
                $imageName = time().'.'.$image->extension();
                $image->move(public_path('uploads/inventory'), $imageName);
                $data['thumbnail_image'] = 'uploads/inventory/'.$imageName;
            }

            // Create a new inventory with the validated data
            $inventory = Inventory::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory created successfully',
                'data' => $inventory,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create inventory: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single inventory
     */
    public function updateInventory(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $inventory = Inventory::find($id);

            // Validate the request data
            $data = $request->validate([
                'name' => 'sometimes|required|string',
                'thumbnail_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'description' => 'sometimes|required|string',
                'material_type' => 'sometimes|required|string',
                'stock' => 'sometimes|required|integer',
                'cost_price' => 'sometimes|required|numeric',
                'manufacturing' => 'sometimes|required|string',
                'supplier' => 'sometimes|required|string',
                'serial_number' => 'sometimes|required|string|unique:inventories,serial_number,'.$id,
                'SKU_id' => 'sometimes|required|integer',
            ]);

            // Find the inventory by its ID
            $inventory = Inventory::findOrFail($id);

            //Handle image upload
            if ($request->hasFile('thumbnail_image')) {
                $image = $request->file('thumbnail_image');
                $imageName = time().'.'.$image->extension();
                $image->move(public_path('uploads/inventory'), $imageName);
                $data['thumbnail_image'] = 'uploads/inventory/'.$imageName;
            }

            // Update the inventory with the new data
            $inventory->update($data);

            // Return a JSON response with the status, message, and the updated inventory
            return response()->json([
                'status' => 'success',
                'message' => 'Inventory updated successfully',
                'data' => $inventory,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update inventory: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete inventory
     */
    public function deleteInventory($id): \Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::find($id);

        if (! $inventory) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Inventory not found',
                'data' => null,
            ], 404);
        }

        // Delete the inventory
        $inventory->delete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory deleted successfully',
            'data' => null,
        ], 200);
    }

    /**
     * Restore inventory
     */
    public function restoreInventory($id): \Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::withTrashed()->find($id);

        if (! $inventory) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Inventory not found',
                'data' => 'null',
            ], 404);
        }

        // Restore the inventory
        $inventory->restore();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory restored successfully',
            'data' => $inventory,
        ], 200);
    }

    /**
     * Permanently delete inventory
     */
    public function permanentDeleteInventory($id): \Illuminate\Http\JsonResponse
    {
        // Find the inventory by its ID
        $inventory = Inventory::withTrashed()->find($id);

        if (! $inventory) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Inventory not found',
                'data' => null,
            ], 404);
        }

        // Delete the inventory permanently
        $inventory->forceDelete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory permanently deleted',
            'data' => null,
        ], 200);
    }
}
