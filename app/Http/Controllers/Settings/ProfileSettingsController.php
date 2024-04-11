<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
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
            $request->validate([
                'name' => 'sometimes|required',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
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
                $image_name = Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/profile/');
                $image->move($destinationPath, $image_name);
                $image_location = 'uploads/profile/' . $image_name;
            }

            $user = User::findOrFail($id);
            $user->fill($request->only(['name', 'email', 'phone_number', 'city', 'state', 'country', 'zip_code', 'language', 'image']));
            $user->save();

            $user_data = $user->only('name', 'email', 'phone_number', 'city', 'state', 'country', 'zip_code', 'language', 'image');

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user_data], 200);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }
}
