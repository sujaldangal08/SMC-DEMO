<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    public function company(): \Illuminate\Http\JsonResponse
    {
        // Fetch all companies from the database
        $companies = Company::with('branches')->get();
        dd($companies);
        // Count the total number of companies
        $CompanyCount = Company::count();

        // Return a JSON response with the status, message, total number of companies, and the company data
        return response()->json([
            'status' => 'success',
            'message' => 'Companies retrieved successfully',
            'total' => $CompanyCount,
            'data' => Company::all()
        ], 200);

    }
    public function updateCompany(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try { // Validate the incoming request data
            $validatedData = $request->validate([
                'company_name' => 'string|max:255',
                'company_street' => 'string|max:255',
                'company_city' => 'string|max:255',
                'company_state' => 'string|max:255',
                'company_zip' => 'string',
                'company_phone' => 'numeric|digits:10',
                'company_email' => 'email',
                'company_code' => 'sometimes|unique:branches,branch_code,' . $id,
                'company_country_id' => ''
            ],  [
                'company_code.unique' => 'The company code has already been taken.'
            ]);

            // Find the company record in the database
            $company = Company::findOrFail($id);

            // Update the company record with the validated data
            $company->update($validatedData);

            // Return a JSON response with the status, message, and the updated company data
            return response()->json([
                'status' => 'success',
                'message' => 'Company updated successfully',
                'data' => $company
            ], 200);
        } catch (ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to Update: Validation error',
                'errors' => $e->errors()
            ], 400);
        }

    }

}
