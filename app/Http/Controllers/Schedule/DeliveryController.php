<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Traits\ValidatesRoles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryController extends Controller
{
    use ValidatesRoles;

    // Method to insert delivery
    public function createDelivery(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'status' => 'required|string',
            'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
            'driver_id' => ['required', $this->roleRule('driver')],
            'customer_id' => ['required', $this->roleRule('customer')],
            'delivery_location' => 'required|string',
            'delivery_start_date' => 'required|date',
            'delivery_end_date' => 'required|date',
            'delivery_start_time' => 'required|string',
            'delivery_end_time' => 'required|string',
            'delivery_file' => 'required|string',
            'delivery_interval' => 'required|string',
            'delivery_status' => 'required|string',
            'delivery_notes' => 'required|string',
        ]);
        // Create a new delivery with the validated data
        $delivery = Delivery::create($data);

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery created successfully',
            'Delivery' => $delivery,
        ], 200);
    }

    // Method to update delivery
    public function updateDelivery(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'status' => 'string',
            'truck_id' => [Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
            'driver_id' => ['nullable', $this->roleRule('driver')],
            'customer_id' => ['required', $this->roleRule('customer')],
            'delivery_location' => 'string',
            'delivery_start_date' => 'date',
            'delivery_end_date' => 'date',
            'delivery_start_time' => 'string',
            'delivery_end_time' => 'string',
            'delivery_file' => 'string',
            'delivery_interval' => 'string',
            'delivery_status' => 'string',
            'delivery_notes' => 'string',
        ]);

        // Find the delivery by ID
        $delivery = Delivery::find($id);

        // Update the delivery with the validated data
        $delivery->update($data);

        // Return the response in JSON format
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery updated',
            'Delivery' => $delivery,
        ], 200);
    }
}
