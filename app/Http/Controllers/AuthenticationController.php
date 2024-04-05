<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\AccountCreation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials)) {
                $tokenResult = $request->user()->createToken('api-token');
                $token = $tokenResult->accessToken;
                $token->expires_at = now()->addHours(1); // Token expires in 1 hour
                $token->save();

                $plainTextToken = $tokenResult->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $plainTextToken,
                ]);
            }
            return response()->json([
                'message' => 'Unauthorized user does not exists'
            ], 401);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email'
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            return response()->json(['message' => 'Account created successfully', 'token' => $token], 201);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()()], 400);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users',
            ]);
            // $password = Str::random(10); Auto generate password of 10 characters
            $password = 'password'; // Default password 'password

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->save();

            Mail::to($request->email)->send(new AccountCreation($request->email, $password,));

            return response()->json([
                'message' => 'User created successfully',
                'password' => $password
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function dashboard()
    {
        return response()->json(['message' => 'Dashboard']);
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Generate a token
            $token = Str::random(60);

            // Save it in the database
            $user->password_reset_token = hash('sha256', $token);
            $user->save();

            // Send the token to the user's email...
            // You can use Laravel's built-in Mail feature to do this.

            return response()->json(['message' => 'Reset link sent to your email']);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }
}
