<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function company(): \Illuminate\Http\JsonResponse
    {
        // Fetch all companies from the database
        Company::all();

        // Count the total number of companies
        $CompanyCount = Company::count();

        // Return a JSON response with the status, message, total number of companies, and the company data
        return response()->json([
            'status' => 'success',
            'message' => 'Companies retrieved successfully',
            'total' => $CompanyCount,
            'data' => Company::all()
        ]);

    }
    public function updateCompany(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try { // Validate the incoming request data
            $validatedData = $request->validate([
                'company_name' => '',
                'company_street' => '',
                'company_city' => '',
                'company_state' => '',
                'company_zip' => '',
                'company_phone' => '',
                'company_email' => '',
                'company_code' => 'unique:companies,company_code',
                'company_country_id' => ''
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

}
