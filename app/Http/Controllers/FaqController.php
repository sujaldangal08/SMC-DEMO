<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Get all faqs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFaq()
    {
        try {
            $faqs = Faq::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All faqs fetched successfully',
                'data' => $faqs,
                'faqs' => $faqs], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Insert a new faq
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertFaq(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);
            $faq = new Faq();
            $faq->title = $request->title;
            $faq->description = $request->description;
            $faq->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Faq added successfully',
                'data' => $faq,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Data not found!',
                'data' => null,
                'error' => $e->getMessage()], 500);
        }
    }
}
