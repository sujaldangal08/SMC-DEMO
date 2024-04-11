<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\sku;
use Illuminate\Validation\ValidationException;


class InventoryController extends Controller
{
    //
    public function inventory(): \Illuminate\Http\JsonResponse
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


    public function warehouse(): \Illuminate\Http\JsonResponse
    {
        try {
            // Fetch all warehouse data from the database
            $warehouseData = sku::all();
            dd($warehouseData);


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
    public function updateInventory(Request $request, $SKU)
    {
        try {
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

            $inventory = Inventory::where('SKU', $SKU)->firstOrFail();
            $inventory->update($validatedData);

            return response()->json($inventory, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
