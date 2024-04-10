<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    //
    public function inventory() :\Illuminate\Http\JsonResponse
    {
        // Fetch all inventories from the database
        Inventory::all();

        // Count the total number of inventories
        $InventoryCount = Inventory::count();

        // Return a JSON response with the status, message, total number of inventories, and the inventory data
        return response()->json([
            'status' => 'success',
            'message' => 'Inventories retrieved successfully',
            'total' => $InventoryCount,
            'data' => Inventory::all()
        ]);
    }
    public function createInventory(Request $request): \Illuminate\Http\JsonResponse
    {
        try { // Validate the incoming request data
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
    public function updateInventory(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            // Find the inventory record with the given ID
            $inventory = Inventory::findOrFail($id);

            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'thumbnail_image' => 'string|max:255',
                'description' => 'string',
                'material_type' => 'string|max:255',
                'stock' => 'integer',
                'cost_price' => 'numeric',
                'manufacturing' => 'string|max:255',
                'supplier' => 'string|max:255',
                'serial_number' => 'string|unique:inventories,serial_number,' . $inventory->id,
            ]);

            // Update the inventory record with the validated data
            $inventory->update($validatedData);

            // Return a JSON response with the status, message, and the updated inventory data
            return response()->json([
                'status' => 'success',
                'message' => 'Inventory updated successfully',
                'data' => $inventory
            ]);
        } catch (ModelNotFoundException $e) {
            // Return a custom error response
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found'
            ], 404);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }
    }

}
