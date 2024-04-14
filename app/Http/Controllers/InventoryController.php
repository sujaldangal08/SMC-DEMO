<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Sku;

class InventoryController extends Controller
{
    // Method to get inventory details
    public function inventory() :\Illuminate\Http\JsonResponse
    {
        // Define SKU ID
        $skuId = 1;
        // Get all SKU
        $sku = Sku::all();
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
    // Method to get warehouse details
    public function warehouse(): \Illuminate\Http\JsonResponse
    {
        $warehouseData = Warehouse::with(['sku'])->get();

        try {
            // Fetch all warehouse data from the database

            // Return a JSON response with the status, message, and the warehouse data
            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse data retrieved successfully',
                'data' => $warehouseData
            ]);
        } catch (\Exception $e) {
            // Return a JSON response if something goes wrong
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    // Method to create a new inventory
    public function createInventory(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'SKU' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'thumbnail_image' => 'required|string|max:255',
                'description' => 'required|string',
                'material_type' => 'required|string|max:255',
                'stock' => 'required|integer',
                'cost_price' => 'required|numeric',
                'manufacturing' => 'required|string|max:255',
                'supplier' => 'required|string|max:255',
                'serial_number' => 'required|string|unique:inventories,serial_number',
            ]);

            // Create a new inventory record in the database
            $inventory = Inventory::create($validatedData);

            // Return a JSON response with the status, message, and the newly created inventory data
            return response()->json([
                'status' => 'success',
                'message' => 'Inventory created successfully',
                'data' => $inventory
            ]);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    // Method to update an existing inventory
    public function updateInventory(Request $request, $SKU)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'string',
                'thumbnail_image' => 'string',
                'description' => 'string',
                'material_type' => 'string',
                'stock' => 'integer',
                'cost_price' => 'numeric',
                'manufacturing' => 'string',
                'supplier' => 'string',
                'serial_number' => 'string|unique:inventories,serial_number,' . $SKU . ',SKU',
            ]);

            // Get the inventory details for the given SKU
            $inventory = Inventory::where('SKU', $SKU)->firstOrFail();

            // Update the inventory details
            $inventory->update($validatedData);

            // Return the updated inventory details
            return response()->json($inventory, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return a custom error response if the inventory is not found
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        } catch (\Exception $e) {
            // Return a JSON response if something goes wrong
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
