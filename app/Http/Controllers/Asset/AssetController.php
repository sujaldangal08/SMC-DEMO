<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
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

    public function getOne(int $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Asset fetched successfully',
                'data' => $asset,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Asset not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function createAsset(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'asset_type' => 'required|string|max:255',
                'meta' => 'required|array',
                'branch_id' => 'required|integer|exists:branches,id',
            ]);

            $image = $request->file('image');
            $imageName = time().'.'.$image->extension();
            $image->move(public_path('uploads/assets'), $imageName);
            $destinationPath = 'uploads/assets/'.$imageName;

            $asset = new Asset();
            $asset->title = $request->title;
            $asset->image = $destinationPath;
            $asset->asset_type = $request->asset_type;
            $asset->meta = $request->meta;
            $asset->branch_id = $request->branch_id;
            $asset->save();

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

    public function updateAsset(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'asset_type' => 'sometimes|required|string|max:255',
                'meta' => 'sometimes|required|array',
                'branch_id' => 'sometimes|required|integer|exists:branches,id',
            ]);

            $asset = Asset::findOrFail($id);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->extension();
                $image->move(public_path('uploads/assets'), $imageName);
                $destinationPath = 'uploads/assets/'.$imageName;
                $asset->image = $destinationPath;
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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Asset not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Asset not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Asset not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Asset not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
