<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class ProfileSettingsController extends Controller
{
    public function updateProfile(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                'city' => 'string',
                'state' => 'string',
                'country' => 'string',
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
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;
            $user->zip_code = $request->zip_code;
            $user->language = $request->language;
            $user->image = $image_location;
            $user->save();
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }
}
