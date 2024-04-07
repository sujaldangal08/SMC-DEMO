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
}
