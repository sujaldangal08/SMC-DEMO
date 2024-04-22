<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesOrder;

class SalesOrderController extends Controller

{
    public function show($id)
    {
        // Find the sales order by its ID
        $salesOrder = SalesOrder::find($id);

        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        // Return the found sales order
        return response()->json(['data' => $salesOrder], 200);
    }

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
                'data' => $salesOrder,
        ], 200);
    }
}
