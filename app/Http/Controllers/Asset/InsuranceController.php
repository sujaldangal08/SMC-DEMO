<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
    /**
     * Get all insurances
     *
     * @return JsonResponse
     */
    public function getAllInsurance()
    {
        try {
            $insurances = Insurance::all();
            $insurances->load([
                'asset:id,title',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'All insurances fetched successfully',
                'data' => $insurances,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get a single insurance
     *
     * @return JsonResponse
     */
    public function getOneInsurance(int $id)
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $insurance->load([
                'asset:id,title',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance fetched successfully',
                'data' => $insurance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Create a new insurance
     */
    public function createInsurance(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'asset_id' => 'required|integer|exists:assets,id',
                'insurance_type' => 'required|string',
                'provider' => 'required|string',
                'amount' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'purchase_date' => 'required|date',
                'attachment.*' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
                'contact_meta' => 'required',

            ]);

            if ($request->hasFile('attachment')) {
                $validatedData = $this->attachmentUpload($validatedData);
            }

            $validatedData['contact_meta'] = json_decode($validatedData['contact_meta'], true);
            $insurance = Insurance::create($validatedData);
            $insurance->load([
                'asset:id,title',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance created successfully',
                'data' => $insurance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update an insurance
     */
    public function updateInsurance(Request $request, int $id): JsonResponse
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $validatedData = $request->validate([
                'asset_id' => 'sometimes|integer|exists:assets,id',
                'insurance_type' => 'sometimes|string',
                'provider' => 'sometimes|string',
                'amount' => 'sometimes|numeric',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'purchase_date' => 'sometimes|date',
                'attachment.*' => 'sometimes|mimes:pdf,jpg,jpeg,png|max:2048',
                'contact_meta' => 'sometimes|array',
            ]);

            if ($request->hasFile('attachment')) {
                $validatedData = $this->attachmentUpload($validatedData);
            }
            if (isset($validatedData['contact_meta'])) {
                $validatedData['contact_meta'] = json_decode($validatedData['contact_meta'], true);
            }
            $insurance->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance updated successfully',
                'data' => $insurance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Delete an insurance
     *
     * @return JsonResponse
     */
    public function deleteInsurance(int $id)
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $insurance->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance deleted successfully',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Restore a soft deleted insurance
     *
     * @return JsonResponse
     */
    public function restoreInsurance(int $id)
    {
        try {
            $insurance = Insurance::withTrashed()->findOrFail($id);
            $insurance->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance restored successfully',
                'data' => $insurance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Permanently delete an insurance
     *
     * @return JsonResponse
     */
    public function permanentDeleteInsurance(int $id)
    {
        try {
            $insurance = Insurance::withTrashed()->findOrFail($id);
            $insurance->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance permanently deleted successfully',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    protected function attachmentUpload(array $validatedRequest)
    {
        $attachments = [];
        foreach ($validatedRequest['attachment'] as $attachment) {
            $attachment_name = Str::random(10).'.'.$attachment->getClientOriginalExtension();
            $filePath = 'uploads/insurance/'.$attachment_name;

            // Check if the 'uploads/insurance' directory exists and create it if it doesn't
            if (! Storage::disk('public')->exists('uploads/insurance')) {
                Storage::disk('public')->makeDirectory('uploads/insurance');
            }
            // Save the attachment to a file in the public directory
            Storage::disk('public')->put($filePath, file_get_contents($attachment));

            $attachment_location = 'uploads/insurance/'.$attachment_name;
            $attachments[] = $attachment_location;
        }
        $validatedRequest['attachment'] = $attachments;

        return $validatedRequest;
    }
}
