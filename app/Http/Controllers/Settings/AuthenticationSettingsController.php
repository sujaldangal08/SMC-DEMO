<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthenticationSettingsController extends Controller
{
    public function authAttempts(): JsonResponse
    {
        try {
            $authAttempts = Role::select('role', 'max_login_attempts')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All login attempts fetched successfully',
                'auth_attempts' => $authAttempts], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to fetch login attempts' . $e->getMessage(),
                'exception' => $e->getMessage()], 400);
        }
    }

    public function getOneAttempt(int $id): JsonResponse
    {
        try {
            $authAttempts = Role::where('id', $id)->select('role', 'max_login_attempts')->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Login attempts fetched successfully',
                'auth_attempts' => $authAttempts], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Validation errors' .  $e->getMessage(),
                'error' => $e->validator->errors()->getMessages()], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to fetch login attempt' . $e->getMessage(),
                'exception' => $e->getMessage()], 400);
        }
    }

    public function updateAttempts(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'max_login_attempts' => 'required|integer|min:1',
            ]);
            $role = Role::findOrFail($id);
            $role->max_login_attempts = $request->max_login_attempts;
            $role->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Login attempts updated',
                'auth_attempts' => $role], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to update login attempts' . $e->getMessage(),
                'exception' => $e->getMessage()], 400);
        }
    }
}
