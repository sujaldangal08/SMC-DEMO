<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    // Delivery
    // Method to get delivery details
    public function delivery(): \Illuminate\Http\JsonResponse
    {
        // Define delivery ID
        $deliveryId = 1;

        // Get delivery details for the given delivery ID
        $delivery = Delivery::where('delivery_id', $deliveryId)->get();

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery retrieved successfully',
            'Delivery' => $delivery
        ]);
    }

    // Method to insert delivery
    public function createDelivery(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'delivery_id' => 'required|integer',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_status' => 'required|string',
            'delivery_driver' => 'required|string',
            'delivery_vehicle' => 'required|string',
            'delivery_company' => 'required|string'
        ]);

        // Create a new delivery with the validated data
        $delivery = Delivery::create($data);

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery created successfully',
            'Delivery' => $delivery
        ], 200);
    }

    // Method to update delivery
    public function updateDelivery(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'delivery_id' => 'required|integer',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_status' => 'required|string',
            'delivery_driver' => 'required|string',
            'delivery_vehicle' => 'required|string',
            'delivery_company' => 'required|string'
        ]);

        // Find the delivery by ID
        $delivery = Delivery::find($id);

        // Update the delivery with the validated data
        $delivery->update($data);

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery updated',
            'Delivery' => $delivery
        ], 200);
    }
}
