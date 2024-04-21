<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Waste;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WastageController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $waste = Waste::all();
            return response()->json([
                'status' => 'success',
                'data' => $waste
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $waste = Waste::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $waste
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // dd($request->all());
            $validatedData = $request->validate([
                'ticket_id' => 'required|integer|exists:tickets,id',
                'quantity' => 'required|integer',
                'image.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg',
                'notes' => 'sometimes|string'
            ]);

            $imagePaths = [];
            foreach ($validatedData['image'] as $image) {
                $imageName = Str::random(10) . '.' . $image->extension();
                $image->move(public_path('uploads/wastes'), $imageName);
                $imagePath = 'uploads/wastes/' . $imageName;
                $imagePaths[] = $imagePath;
            }
            $validatedData['image'] = $imagePaths;

            $data = Ticket::where('id', $validatedData['ticket_id'])->first();
            // dd($data);
            if ($data->gross_weight < $validatedData['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Waste quantity cannot be greater than gross weight'
                ], 400);
            }
            if ($validatedData['quantity'] > ($data->gross_weight * 0.1)) {
                $message = 'Warning: Waste quantity is greater than 10% of gross weight';
            } else {
                $message = 'Ok';
            }
            $waste = Waste::create($validatedData);

            return response()->json([
                'status' => $message,
                'data' => $waste
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'ticket_id' => 'sometimes|integer|exists:tickets,id',
                'quantity' => 'sometimes|integer',
                'image.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,svg',
                'notes' => 'sometimes|string'
            ]);

            $data = Ticket::where('id', $validatedData['ticket_id'])->first();
            // dd($data);
            if ($data->gross_weight < $validatedData['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Waste quantity cannot be greater than gross weight'
                ], 400);
            }
            if ($validatedData['quantity'] > ($data->gross_weight * 0.1)) {
                $message = 'Warning: Waste quantity is greater than 10% of gross weight';
            } else {
                $message = 'Ok';
            }

            $waste = Waste::findOrFail($id);
            $waste->update($validatedData);

            return response()->json([
                'status' => $message,
                'data' => $waste
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $waste = Waste::findOrFail($id);
            $waste->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Waste deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $waste = Waste::withTrashed()->findOrFail($id);
            $waste->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Waste restored successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function permanentDelete(int $id): JsonResponse
    {
        try {
            $waste = Waste::withTrashed()->findOrFail($id);
            $waste->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Waste permanently deleted'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
