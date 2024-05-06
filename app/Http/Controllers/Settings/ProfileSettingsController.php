<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileSettingsController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            if ($request->user()->hasRole('super-admin')) {
                return response()->json(['message' => 'Super-Admin Cannot Access This route'], 401);
            }
            $id = $request->user()->id;
            $user = User::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:users,email,'.$id,
                'phone_number' => 'sometimes|required|regex:/^([0-9\s\-\+\(\)]*)$/|digits:10',
                'city' => 'sometimes|required|string',
                'state' => 'sometimes|required|string',
                'country' => 'sometimes|required|string',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if (isset($validatedData['image'])) {
                $validatedData = $this->imageUpload($validatedData);
                // dd($validatedData);
            }

            $user->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Profile update failed',
                'exception' => $e->getMessage(),
            ], 400);
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
                    'data' => null,
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successful',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Password reset failed',
                'exception' => $e->getMessage(),
            ], 400);
        }
    }

    protected function imageUpload(array $validatedData)
    {
        $image = $validatedData['image'];
        $image_name = Str::random(10).'.'.$image->getClientOriginalExtension();
        $filePath = 'uploads/user/'.$image_name;

        // Check if the 'uploads/user' directory exists and create it if it doesn't
        if (! Storage::disk('public')->exists('uploads/user')) {
            Storage::disk('public')->makeDirectory('uploads/user');
        }
        // Save the image to a file in the public directory
        Storage::disk('public')->put($filePath, file_get_contents($image));

        $image_location = 'uploads/user/'.$image_name;
        $validatedData['image'] = $image_location;

        return $validatedData;
    }
}
