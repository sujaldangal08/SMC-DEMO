<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
    public function getAllInsurance()
    {
        try {
            $insurances = Insurance::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All insurances fetched successfully',
                'data' => $insurances,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOneInsurance(int $id)
    {
        try {
            $insurance = Insurance::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance fetched successfully',
                'data' => [],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createInsurance(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'asset_id' => 'required|integer|exists:assets,id',
                'insurance_type' => 'required|string',
                'provider' => 'required|string',
                'amount' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'purchase_date' => 'required|date',
                'attachment' => 'required|file',
                'contact_meta' => 'required|array',

            ]);

            $attachments = [];
            if ($request->hasFile('attachment')) {
                $files = $request->file('attachment');
                foreach ($files as $file) {
                    $filename = Str::random(10).'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/attachments'), $filename);
                    $attachments[] = 'uploads/attachments/'.$filename;
                }
            }
            $insurance = new Insurance();
            $insurance->asset_id = $request->asset_id;
            $insurance->insurance_type = $request->insurance_type;
            $insurance->provider = $request->provider;
            $insurance->amount = $request->amount;
            $insurance->start_date = $request->start_date;
            $insurance->end_date = $request->end_date;
            $insurance->purchase_date = $request->purchase_date;
            $insurance->attachment = $attachments;
            $insurance->contact_meta = json_decode($request->contact_meta, true);
            $insurance->save();
            $insurance->asset_title = $insurance->asset->title;

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance created successfully',
                'data' => $insurance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error:'.$e->getMessage(),
            ], 500);
        }
    }

    public function updateInsurance(Request $request, int $id): JsonResponse
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $request->validate([
                'asset_id' => 'sometimes|integer|exists:assets,id',
                'insurance_type' => 'sometimes|string',
                'provider' => 'sometimes|string',
                'amount' => 'sometimes|numeric',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'purchase_date' => 'sometimes|date',
                'attachment' => 'sometimes|file',
                'contact_meta' => 'sometimes|array',
            ]);

            $attachments = [];
            if ($request->hasFile('attachment')) {
                $files = $request->file('attachment');
                foreach ($files as $file) {
                    $filename = Str::random(10).'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/attachments'), $filename);
                    $attachments[] = 'uploads/attachments/'.$filename;
                }
            }
            $insurance->asset_id = $request->asset_id ?? $insurance->asset_id;
            $insurance->insurance_type = $request->insurance_type ?? $insurance->insurance_type;
            $insurance->provider = $request->provider ?? $insurance->provider;
            $insurance->amount = $request->amount ?? $insurance->amount;
            $insurance->start_date = $request->start_date ?? $insurance->start_date;
            $insurance->end_date = $request->end_date ?? $insurance->end_date;
            $insurance->purchase_date = $request->purchase_date ?? $insurance->purchase_date;
            $insurance->attachment = $attachments ?? $insurance->attachment;
            $insurance->contact_meta = json_decode($request->contact_meta, true) ?? $insurance->contact_meta;
            $insurance->save();

            $insurance->asset_title = $insurance->asset->title;

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance updated successfully',
                'data' => $insurance,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error:'.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteInsurance(int $id)
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $insurance->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error:'.$e->getMessage(),
            ], 500);
        }
    }

    public function restoreInsurance(int $id)
    {
        try {
            $insurance = Insurance::withTrashed()->findOrFail($id);
            $insurance->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance restored successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error:'.$e->getMessage(),
            ], 500);
        }
    }

    public function permanentDeleteInsurance(int $id)
    {
        try {
            $insurance = Insurance::withTrashed()->findOrFail($id);
            $insurance->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Insurance permanently deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error:'.$e->getMessage(),
            ], 500);
        }
    }
}
