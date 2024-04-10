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

}
