<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function branch()
    {
        Branch::all();
        $BranchCount = Branch::count();

        return response()->json([
            'status' => 'success',
            'message' => 'Branches retrieved successfully',
            'total' => $BranchCount,
            'data' => Branch::all()
        ]);
    }
}
