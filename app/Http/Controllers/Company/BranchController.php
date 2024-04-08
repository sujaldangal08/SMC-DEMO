<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

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
        try { // Validate the incoming request data
            $validatedData = $request->validate([
                'branch_name' => 'required|string|max:255',
                'branch_street' => 'required|string|max:255',
                'branch_city' => 'required|string|max:255',
                'branch_state' => 'required|string|max:255',
                'branch_zip' => 'required|string',
                'branch_phone' => 'required|numeric|digits:10',
                'branch_email' => 'required|email|unique:branches,branch_email',
                'branch_code' => 'required | unique:branches,branch_code',
                'branch_status' => 'required',
                'branch_country_id' => 'required',
                'company_id' => 'required|exists:companies,id'
            ]);



            // Create a new branch record in the database
            $branch = Branch::create($validatedData);

            // Return a JSON response with the status, message, and the newly created branch data
            return response()->json([
                'status' => 'success',
                'message' => 'Branch created successfully',
                'data' => $branch
            ]);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function updateBranch(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try { // Validate the incoming request data
            $validatedData = $request->validate([
                'branch_name' => 'string|max:255',
                'branch_street' => 'string|max:255',
                'branch_city' => 'string|max:255',
                'branch_state' => 'string|max:255',
                'branch_zip' => '',
                'branch_phone' => 'numeric|digits:10',
                'branch_email' => 'email',
                'branch_code' => 'sometimes|unique:branches,branch_code,' . $id,
                'branch_status' => '',
                'branch_country_id' => '',
                'company_id' => 'exists:companies,id'
            ]);

            // Find the branch record in the database
            $branch = Branch::findOrFail($id);

            // Update the branch record with the validated data
            $branch->update($validatedData);

            // Return a JSON response with the status, message, and the updated branch data
            return response()->json([
                'status' => 'success',
                'message' => 'Branch updated successfully',
                'data' => $branch
            ]);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to Update: Validation error',
                'errors' => $e->errors()
            ], 400);
        }
    }

    public function deleteBranch($id): \Illuminate\Http\JsonResponse
    {
        // Find the branch record in the database
        $branch = Branch::findOrFail($id);

        // Delete the branch record
        $branch->delete();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully'
        ]);
    }

    public function restoreBranch($id): \Illuminate\Http\JsonResponse
    {
        // Find the branch record in the database
        $branch = Branch::withTrashed()->findOrFail($id);

        // Restore the branch record
        $branch->restore();

        // Return a JSON response with the status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Branch recovered successfully'
        ]);
    }

    public function permanentDeleteBranch($id): \Illuminate\Http\JsonResponse
    {
        // Find the soft deleted branch record in the database
        $branch = Branch::onlyTrashed()->where('id', $id)->first();

        // Check if the branch record exists
        if ($branch) {
            // Permanently delete the soft deleted branch record
            $branch->forceDelete();

            // Return a JSON response with the status and message
            return response()->json([
                'status' => 'success',
                'message' => 'Branch permanently deleted successfully!'
            ]);
        } else {
            // Return a JSON response with the status and message
            return response()->json([
                'status' => 'error',
                'message' => 'No deleted branch found with the given ID'
            ]);
        }
    }
}
