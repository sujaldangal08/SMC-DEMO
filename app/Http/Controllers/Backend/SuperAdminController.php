<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function createSuperAdmin(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]);

            $user = new Backend();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            // $user->password = bcrypt($validatedData['password']);
            $user->password = password_hash($validatedData['password'], PASSWORD_DEFAULT);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Super admin created successfully!',
                'data' => $user,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Validation error.',
                'data' => null,
                'errors' => $e->validator->errors()->getMessages(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to create super admin: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        $superAdmin = Backend::find($id);
        if (! $superAdmin) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Super admin not found',
                'data' => null,
            ], 404);
        }
        $superAdmin->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Super admin moved to trash successfully',
            'data' => null,
        ], 200);
    }
}
