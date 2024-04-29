<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    /**
     * Get all sales orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Find the sales order by its ID
        $salesOrder = SalesOrder::find($id);

        if (! $salesOrder) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Sales order not found',
                'data' => null,
            ], 404);
        }

        // Return the found sales order
        return response()->json([
            'status' => 'success',
            'message' => 'Sales order fetched successfully',
            'data' => $salesOrder,
        ], 200);
    }

    /**
     * Insert a new sales order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     // Define validation rules for your sales order fields
        //     'customer_name' => 'required|string',
        //     'total_amount' => 'required|numeric',
        //     // Add more fields as needed
        // ]);

        // // Create a new sales order instance
        // $salesOrder = new SalesOrder();
        // $salesOrder->customer_name = $validatedData['customer_name'];
        // $salesOrder->total_amount = $validatedData['total_amount'];
        // // Assign other fields as needed
        // $salesOrder->save();

        // Return a response indicating success

        $salesOrder = SalesOrder::get();

        return response()->json([
            'status' => 'success',
            'message' => 'Sales order created successfully',
            'data' => $salesOrder,
        ], 200);
    }
}
