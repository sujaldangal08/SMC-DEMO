<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'data' => Branch::all(),
        ], 200);
    }

    public function branchSingle($id): \Illuminate\Http\JsonResponse
{
    try {
        $data = Branch::findOrFail($id);
        return response()->json([
            'id' => $data->id,
            'branch_name' => $data->branch_name,
            'branch_street' => $data->branch_street,
            'branch_street2' => $data->branch_street2,
            'branch_city' => $data->branch_city,
            'branch_state' => $data->branch_state,
            'branch_zip' => $data->branch_zip,
            'branch_phone' => $data->branch_phone,
            'branch_email' => $data->branch_email,
            'branch_code' => $data->branch_code,
            'branch_status' => $data->branch_status,
            'branch_country_id' => $data->branch_country_id
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'failure',
            'message' => 'Resource not found.',
            'data' => null,
            'error' => 'Resource not found.'
        ], 404);
    }
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
                'company_id' => 'required|exists:companies,id',
            ]);

            // Create a new branch record in the database
            $branch = Branch::create($validatedData);

            // Return a JSON response with the status, message, and the newly created branch data
            return response()->json([
                'status' => 'success',
                'message' => 'Branch created successfully',
                'data' => $branch,
            ], 201);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'failure',
                'message' => 'Validation error',
                'data' => null,
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
                'branch_code' => 'sometimes|unique:branches,branch_code,'.$id,
                'branch_status' => '',
                'branch_country_id' => '',
                'company_id' => 'exists:companies,id',
            ], [
                'branch_code.unique' => 'The branch code has already been taken.',
            ]);

            // Find the branch record in the database
            $branch = Branch::findOrFail($id);

            // Update the branch record with the validated data
            $branch->update($validatedData);

            // Return a JSON response with the status, message, and the updated branch data
            return response()->json([
                'status' => 'success',
                'message' => 'Branch updated successfully',
                'data' => $branch,
            ], 200);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'failure',
                'message' => 'Unable to Update: Validation error',
                'data' => null,
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
            'message' => 'Branch deleted successfully',
            'data' => null
        ], 200);
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
            'message' => 'Branch recovered successfully',
            'data' => $branch
        ], 200);
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
                'message' => 'Branch permanently deleted successfully!',
                'data' => null
            ], 200);
        } else {
            // Return a JSON response with the status and message
            return response()->json([
                'status' => 'failure',
                'message' => 'No deleted branch found with the given ID',
                'data' => null
            ], 404);
        }
    }
}
