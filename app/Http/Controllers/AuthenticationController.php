<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\AccountCreation;
use App\Models\Backend;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || ($user->login_attempts >= $user->role->max_login_attempts)) {
                $user->deactivate();
                return response()->json(['message' => 'You account has been deactivated. Pleas contact your admin in order to activate it again'], 401);
            }

            if (auth()->attempt($credentials)) {
                $user->resetLoginAttempts();

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
            $user->incrementLoginAttempts();
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function register(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password'
            ]);

            $role = Role::where('role', 'customer')->first();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role_id = $role->id;
            $user->password = Hash::make($request->password);

            $user->save();

            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json(['message' => 'Account created successfully', 'token' => $plainTextToken], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->getMessages()], 401);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()()], 400);
        }
    }

    public function createUser(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users',
                'role' => 'in:admin,customer,staff,super-admin,driver,manager'
            ]);

            echo 'User created successfully';

            // $password = Str::random(10); Auto generate password of 10 characters
            $password = 'password'; // Default password 'password

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->save();

            //  Mail::to($request->email)->send(new AccountCreation($request->email, $password,));

            return response()->json([
                'message' => 'User created successfully',
                'password' => $password,
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->getMessages()], 401);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }

    public function dashboard(): JsonResponse
    {
        return response()->json(['message' => 'Dashboard']);
    }

    public function forgotPassword(Request $request): JsonResponse
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

    // This is a separate login moult for the backend users

    public function backendLogin(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = Backend::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $plainTextToken,
            ]);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }
    }
}
