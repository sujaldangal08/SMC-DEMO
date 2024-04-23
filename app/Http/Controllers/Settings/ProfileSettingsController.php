<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Hash;

class ProfileSettingsController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            if ($request->user()->hasRole('super-admin')) {
                return response()->json(['message' => 'Super-Admin Cannot Access This route'], 401);
            }
            $id = $request->user()->id;
            $request->validate([
                'name' => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:users,email,'.$id,
                'phone_number' => 'sometimes|required|regex:/^([0-9\s\-\+\(\)]*)$/|digits:10',
                'city' => 'sometimes|required|string',
                'state' => 'sometimes|required|string',
                'country' => 'sometimes|required|string',
            ]);

            if ($request->has('image')) {
                $request->validate([
                    'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
                ]);
                $image = $request->file('image');
                $image_name = Str::random(10).'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/profile/');
                $image->move($destinationPath, $image_name);
                $image_location = 'uploads/profile/'.$image_name;
            }

            $user = User::findOrFail($id);
            $user->fill($request->only(['name', 'email', 'phone_number', 'city', 'state', 'country', 'zip_code', 'language', 'image']));
            $user->save();

            $user_data = $user->only('name', 'email', 'phone_number', 'city', 'state', 'country', 'zip_code', 'language', 'image');

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user_data], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Profile update failed',
                'exception' => $e->getMessage()], 400);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Check if the user is authenticated
        if (! $request->user()) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'confirm_password' => 'required|same:new_password',
            ]);

            $user = $request->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Current password does not match',
                    'data' => null
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successful',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Password reset failed',
                'exception' => $e->getMessage()
            ], 400);
        }
    }

}
