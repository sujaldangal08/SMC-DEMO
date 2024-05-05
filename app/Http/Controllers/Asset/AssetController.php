<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Get all assets
     */
    public function getAll(): JsonResponse
    {
        try {
            $assets = Asset::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All assets fetched successfully',
                'total' => $assets->count(),
                'data' => $assets,
            ], 200); // Return a 200 response code
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500); // Internal Server Error
        }
    }

    /**
     * Get a single asset
     */
    public function getOne(int $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Asset fetched successfully',
                'data' => $asset,
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
     * Create a new asset
     */
    public function createAsset(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'asset_type' => 'required|string|max:255|in:vehicle,equipment,weighing_machine, office_equipment',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'rego_number' => 'required_if:asset_type,vehicle|string|max:255',
                'meta' => 'required',
                'branch_id' => 'required|integer|exists:branches,id',
            ]);
            $validatedData = $this->imageUpload($validatedData);
            $validatedData['meta'] = json_decode($validatedData['meta'], true);
            $asset = Asset::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Asset created successfully',
                'data' => $asset,
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
     * Update an asset
     */
    public function updateAsset(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'rego_number' => 'sometimes|required|string|max:255', // Add 'sometimes' to make this field 'optional
                'asset_type' => 'sometimes|required|string|max:255',
                'meta' => 'sometimes|required|string',
                'branch_id' => 'sometimes|required|integer|exists:branches,id',
            ]);

            $asset = Asset::findOrFail($id);
            if ($request->hasFile('image')) {
                $validatedData = $this->imageUpload($request->all());
                $asset->image = $validatedData['image'];
            }

            $meta = $asset->meta;  // Retrieve the 'meta' array
            $meta = array_merge($meta, $request->get('meta', []));
            $meta = array_filter($meta, 'strlen');  // Remove keys with null or empty values

            $asset->title = $request->title ?? $asset->title;
            $asset->asset_type = $request->asset_type ?? $asset->asset_type;
            $asset->meta = $meta;
            $asset->branch_id = $request->branch_id ?? $asset->branch_id;
            $asset->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset updated successfully',
                'data' => $asset,
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
     * Delete an asset
     */
    public function deleteAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);
            $asset->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset deleted successfully',
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
     * Restore a soft-deleted asset
     */
    public function restoreAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset restored successfully',
                'data' => $asset,
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
     * Permanently delete an asset
     */
    public function permanentDeleteAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset permanently deleted',
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

    protected function imageUpload(array $validatedRequest)
    {
        $image = $validatedRequest['image'];
        $image_name = Str::random(10) . '.' . $image->getClientOriginalExtension();
        $filePath = 'uploads/asset/' . $image_name;

        // Check if the 'uploads/asset' directory exists and create it if it doesn't
        if (!Storage::disk('public')->exists('uploads/asset')) {
            Storage::disk('public')->makeDirectory('uploads/asset');
        }
        // Save the image to a file in the public directory
        Storage::disk('public')->put($filePath, file_get_contents($image));

        $image_location = 'uploads/asset/' . $image_name;
        $validatedRequest['image'] = $image_location;

        return $validatedRequest;
    }
}
