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

    // Method to get warehouse details
    public function warehouse(): \Illuminate\Http\JsonResponse
    {
        $warehouseData = Warehouse::with(['sku'])->get();


            dd($warehouseData);

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

}
