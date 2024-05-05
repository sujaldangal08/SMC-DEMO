<?php

namespace App\Http\Controllers\AdHoc;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdHocRequest;
use App\Models\AdHoc;
use App\Traits\ValidatesRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdHocController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        try {
            $adHoc = AdHoc::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All AdHoc fetched successfully',
                'total' => $adHoc->count(),
                'data' => $adHoc,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOne(int $id): JsonResponse
    {
        try {
            $adHoc = AdHoc::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'AdHoc fetched successfully',
                'data' => $adHoc,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function create(AdHocRequest $request): JsonResponse
    {
        try {
            $validatesData = $request->validated();
            $validatesData = $this->imageUpload($validatesData);

            $adHoc = AdHoc::create($validatesData);

            return response()->json([
                'status' => 'success',
                'message' => 'AdHoc created successfully',
                'data' => $adHoc,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function imageUpload(array $validatedRequest)
    {
        $images = [];
        foreach ($validatedRequest['attachment'] as $attachment) {
            $image_name = Str::random(10) . '.' . $attachment->getClientOriginalExtension();
            $filePath = 'uploads/pickup/' . $image_name;

            // Check if the 'uploads/pickup' directory exists and create it if it doesn't
            if (!Storage::disk('public')->exists('uploads/pickup')) {
                Storage::disk('public')->makeDirectory('uploads/pickup');
            }
            // Save the attachment to a file in the public directory
            Storage::disk('public')->put($filePath, file_get_contents($attachment));

            $image_location = '/uploads/pickup/' . $image_name;
            $images[] = $image_location;
        }
        $validatedRequest['attachment'] = $images;

        return $validatedRequest;
    }
}
