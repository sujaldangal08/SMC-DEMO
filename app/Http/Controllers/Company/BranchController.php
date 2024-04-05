<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function branch(): \Illuminate\Http\JsonResponse
    {
        // Fetch all branches from the database
        Branch::all();

        // Count the total number of branches
        $BranchCount = Branch::count();

        // Return a JSON response with the status, message, total number of branches, and the branch data
        return response()->json([
            'status' => 'success',
            'message' => 'Branches retrieved successfully',
            'total' => $BranchCount,
            'data' => Branch::all()
        ]);
    }

    public function createBranch(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'branch_name' => 'required',
            'branch_street' => 'required',
            'branch_city' => 'required',
            'branch_state' => 'required',
            'branch_zip' => 'required',
            'branch_phone' => 'required',
            'branch_email' => 'required',
            'branch_code' => 'required',
            'branch_status' => 'required',
            'branch_country_id' => 'required'
        ]);

        // Create a new branch record in the database
        $branch = Branch::create([
            'branch_name' => $request->branch_name,
            'branch_street' => $request->branch_street,
            'branch_city' => $request->branch_city,
            'branch_state' => $request->branch_state,
            'branch_zip' => $request->branch_zip,
            'branch_phone' => $request->branch_phone,
            'branch_email' => $request->branch_email,
            'branch_code' => $request->branch_code,
            'branch_status' => $request->branch_status,
            'branch_country_id' => $request->branch_country_id
        ]);

        // Return a JSON response with the status, message, and the newly created branch data
        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully',
            'data' => $branch
        ]);
    }
}
