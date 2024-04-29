<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use Illuminate\Http\Request;

class SkuController extends Controller
{
    /**
     * Get all SKU
     */
    public function sku(): \Illuminate\Http\JsonResponse
    {
        $skuData = Sku::all();

        return response()->json([
            'status' => 'success',
            'message' => 'SKU retrieved successfully',
            'data' => $skuData,
        ], 200);
    }

    /**
     * Get a single SKU
     *
     * @param  $id
     */
    public function createSku(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'name' => 'required|string',
                'barcode' => 'required|string',
                'tags' => 'required|string',
                'status' => 'required|string',
            ]);

            $skuCode = 'SKU'.str_pad((Sku::max('id') + 1), 5, '0', STR_PAD_LEFT);

            // Create a new SKU with the generated SKU code
            $sku = Sku::create(array_merge($data, ['SKU' => $skuCode]));

            // Return a JSON response with the status, message, and the created SKU
            return response()->json([
                'status' => 'success',
                'message' => 'SKU created successfully',
                'data' => $sku,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create SKU: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a single SKU
     */
    public function updateSku(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'name' => 'sometimes|required|string',
                'barcode' => 'sometimes|required|string',
                'tags' => 'sometimes|required|string',
                'status' => 'sometimes|required|string',
            ]);

            // Find the SKU by its ID
            $sku = Sku::find($id);

            if (! $sku) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'SKU not found',
                ], 404);
            }

            // Update the SKU with the new data
            $sku->update($data);

            // Return a JSON response with the status, message, and the updated SKU
            return response()->json([
                'status' => 'success',
                'message' => 'SKU updated successfully',
                'data' => $sku,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update SKU: '.$e->getMessage(),
            ], 500);
        }
    }
}
