<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Asset;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                'data' => $assets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getOne(int $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Asset fetched successfully',
                'data' => $asset
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createAsset(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'asset_type' => 'required',
            'meta' => 'required|array'
        ]);

        // $image = $request->file('image');
        // $imageName = time() . '.' . $image->extension();
        // $image->move(public_path('uploads/assets'), $imageName);
        // $destinationPath = 'uploads/assets/' . $imageName;

        $asset = new Asset();
        $asset->title = $request->title;
        // $asset->image = $destinationPath;
        $asset->asset_type = $request->asset_type;
        $asset->meta = $request->meta;
        $asset->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Asset created successfully',
            'data' => $asset
        ]);
    }

    public function updateAsset(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|required',
                'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'asset_type' => 'sometimes|required',
                'meta' => 'sometimes|required|array'
            ]);

            $asset = Asset::findOrFail($id);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->extension();
                $image->move(public_path('uploads/assets'), $imageName);
                $destinationPath = 'uploads/assets/' . $imageName;
                $asset->image = $destinationPath;
            }

            $meta = $asset->meta;  // Retrieve the 'meta' array
            $meta = array_merge($meta, $request->get('meta', []));
            $meta = array_filter($meta, 'strlen');  // Remove keys with null or empty values


            $asset->title = $request->title ?? $asset->title;
            $asset->asset_type = $request->asset_type ?? $asset->asset_type;
            $asset->meta = $meta;
            $asset->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset updated successfully',
                'data' => $asset
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);
            $asset->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function restoreAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset restored successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function permanentDeleteAsset(int $id): JsonResponse
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset permanently deleted'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
