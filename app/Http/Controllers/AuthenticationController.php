<?php

namespace App\Http\Controllers;

use App\Mail\BrevoEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Backend;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Role;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class AuthenticationController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'Invalid Credentials'], 401);
            }

            if ($user->login_attempts >= $user->role->max_login_attempts) {
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
                'role_id' => 'required|exists:roles,id',
                'branch_id' => 'nullable|exists:branches,id'
            ]);

            if (Role::where('id', $request->role_id)->first()->role == 'customer' && $request->has('branch_id')) {
                return response()->json(['message' => 'You cannot assign customer to a branch'], 401);
            }

            // $password = Str::random(10); Auto generate password of 10 characters
            $password = 'password'; // Default password 'password

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->role_id = $request->role_id;
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
                    'email' => 'required|email|exists:users,email'
                ]);

                $user = User::where('email', $request->email)->first();

                $otp = rand(100000, 999999);
                $user->otp = Hash::make($otp);
                $user->save();
                $fullname = $user->name;
                $subject = 'OTP for password reset';
                $message = $otp;
            return response()->json(['message' => 'OTP sent successfully']);
       }
         catch (\Exception $e) {
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

    public function twoFactorGenerate($userID): JsonResponse
    {
        $user = User::where('id', $userID)->first();
        $check2fa = $user->tfa_secret;
        if($check2fa){
            return response()->json(['message' => '2FA already enabled']);
        }else{
            $google2fa = new Google2FA();
            $companyName = env('APP_NAME');
            $companyEmail = 'nujan@shotcoder.com';
            $secretKey = $google2fa->generateSecretKey();

            // Save the secret key to the user's record
            $user->tfa_secret = $secretKey;
            $user->save();

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                $companyName,
                $companyEmail,
                $secretKey
            );
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);

            $qrCode = $writer->writeString($qrCodeUrl);

            // Define the file path
            $filePath = 'qrcodes/' . Str::random(10) . '.svg';

            // Check if the 'qrcodes' directory exists and create it if it doesn't
            if (!Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }

            // Save the QR code to a file in the public directory
            Storage::disk('public')->put($filePath, $qrCode);

            return response()->json(['qr_code_url' => url(Storage::url($filePath))]);
        }


    }

    public function verify2FACode(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|numeric',
            'user' => 'required|integer',
        ]);

        $google2fa = new Google2FA();

        // Retrieve the secret key from your storage
        $secretKey = User::where('id',  $request->user)->first()->tfa_secret;

        // Ensure that the secret key is a string
        $secretKey = (string) $secretKey;

        // Ensure that the OTP is a string
        $otp = (string) $request->otp;

        $isValid = $google2fa->verifyKey($secretKey, $otp);

        if ($isValid) {
            return response()->json(['message' => '2FA code verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid 2FA code'], 400);
        }
    }

}
