<?php

namespace App\Http\Controllers;

use App\Mail\EmailTemplate;
use App\Models\Backend;
use App\Models\Role;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthenticationController extends Controller
{
    /**
     * Login a user and return a token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (! $user) {
                return response()->json(['message' => 'Invalid Credentials 1'], 401);
            }

            if ($user['login_attempts'] >= $user->role->max_login_attempts) {
                $user->deactivate();

                return response()->json(['message' => 'You account has been deactivated. Please contact your admin in order to activate it again'], 401);
            }

            if (auth()->attempt($credentials)) {
                $user->resetLoginAttempts();
                if ($user->role->role === 'customer' && $user->email_verified_at === null) {
                    return response()->json(['message' => 'Please verify your email'], 401);
                }

                if (! Hash::check($credentials['password'], $user['password'])) {
                    $user->incrementLoginAttempts();

                    return response()->json(['message' => 'Invalid Credentials'], 401);
                }
            }

            // Create a new token for the user
            $tokenResult = $user->createToken('authToken');

            // Return the token in the response
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'f2fa' => (bool) $user->is_tfa,
                '2fa' => (bool) $user->is_tfa,
                'access_token' => $tokenResult->plainTextToken,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'confirm_password' => 'required|same:password',
            ], [
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'confirm_password.required' => 'The confirmation password field is required.',
                'confirm_password.same' => 'The confirmation password must match the password.',

            ]);

            $role = Role::where('role', 'customer')->first();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role_id = $role->id;
            $user->password = Hash::make($request->password);

            $otp = rand(100000, 999999);
            $user->otp = $otp;

            // Set otp_expiry to be 5 minutes from now
            $user->otp_expiry = Carbon::now()->addMinutes(5);
            $username = $request->name;

            $welcomeTemplate = \App\Models\EmailTemplate::where('template_type', 'welcome')->first();

            $subjectWelcome = $welcomeTemplate->subject; // Retrieve the subject from the emailTemplate model
            $welcome_type = $welcomeTemplate->template_type; // Retrieve the template type from the emailTemplate model

            $mailableWelcome = new EmailTemplate($username, $subjectWelcome, $welcome_type);
            Mail::to($user->email)->send($mailableWelcome);

            $emailTemplate = \App\Models\EmailTemplate::where('template_type', 'otp')->first();
            $subject = $emailTemplate->subject; // Retrieve the subject from the emailTemplate model
            $template_type = $emailTemplate->template_type; // Retrieve the template type from the emailTemplate model

            $mailable = new EmailTemplate($username, $subject, $template_type, $otp);
            Mail::to($user->email)->send($mailable);

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully, please check your email for the OTP',
                'data' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'errors' => $e->validator->errors(),
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Verify the OTP sent to the user's email
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        // Fetch the user record based on the email provided in the request
        $checkUser = User::where('email', $request->email)->first();

        // Get the current time and convert it to a Unix timestamp
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $second = strtotime($now);

        // Convert the OTP expiry time to a Unix timestamp
        $secondTwo = strtotime($checkUser->otp_expiry);
        $checkOtp = Crypt::decryptString($checkUser->otp);
        $decodedOtp = json_decode($checkOtp, true);
        dd($decodedOtp);


        // Check if the current time is greater than or equal to the OTP expiry time
        if ($second >= $secondTwo) {
            // If the OTP has expired, return a JSON response with an error message
            return response()->json(['message' => 'OTP has expired'], 401);
        } elseif (Crypt::decryptString($checkUser->otp) === $request->otp) {
            // If the OTP provided in the request matches the OTP stored in the user record,
            $checkUser->email_verified_at = Carbon::now(); // set the email_verified_at field to the current time
            $checkUser->otp = null; // set the otp field to null

            $otp_hash = Crypt::encryptString(Carbon::now()->toDateTimeString().'_'.Str::random(10));

            $checkUser->otp_hash = $otp_hash;

            $checkUser->save();

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully.',
                'hash' => $checkUser->otp_hash,
                'data' => null,
            ], 200);
        } else {
            // If the OTP provided in the request does not match the OTP stored in the user record,
            // return a JSON response with an error message
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid OTP',
                'data' => null,
            ], 401);
        }
    }

    /**
     * Resend the OTP to the user's email
     */
    public function createUser(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users',
                'role_id' => 'required|exists:roles,id',
                'branch_id' => 'nullable|exists:branches,id',
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
                'status' => 'success',
                'message' => 'User created successfully',
                'password' => $password,
                'user' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Validation error',
                'error' => $e->validator->errors()->getMessages(),
                'data' => null,
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Logout a user and revoke the token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Reset the user's password
     *
     * @param  Request  $request
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard',
            'data' => 'Welcome to the dashboard',
        ], 200);
    }

    /**
     * Reset the user's password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
            $otp = rand(100000, 999999);
            $user->otp = Crypt::encryptString($otp);
            $user->otp_expiry = Carbon::now()->addMinutes(5);
            $user->save();
            $username = $user->name;

            $emailTemplate = \App\Models\EmailTemplate::where('template_type', 'otp')->first(); // Replace 1 with the ID of the email template you want to fetch
            $subject = $emailTemplate->subject; // Retrieve the subject from the emailTemplate model
            $template_type = $emailTemplate->template_type; // Retrieve the template type from the emailTemplate model

            // Create a new instance of the mailable and pass the email template to it
            $mailable = new EmailTemplate($username, $subject, $template_type, $otp);

            // Send the email
            Mail::to($user->email)->send($mailable); // Replace 'recipient@example.com' with the recipient's email address

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent to your email',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Reset the user's password
     */
    public function backendLogin(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = Backend::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $plainTextToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Reset the user's password
     */
    public function twoFactorGenerate(Request $request): JsonResponse
    {
        $user = User::where('id', $request->user)->first();
        $check2fa = $user->is_tfa;
        if ($check2fa) {
            return response()->json([
                'status' => 'failure',
                'message' => '2FA already enabled',
                'data' => null,
            ]);
        } else {
            $google2fa = new Google2FA();
            $companyName = env('APP_NAME');
            $companyEmail = $user->email;
            $secretKey = $google2fa->generateSecretKey();

            // Save the secret key to the user's record
            $user->tfa_secret = encrypt($secretKey);
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
            $filePath = 'qrcodes/'.Str::random(10).'.svg';

            // Check if the 'qrcodes' directory exists and create it if it doesn't
            if (! Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }

            // Save the QR code to a file in the public directory
            Storage::disk('public')->put($filePath, $qrCode);

            return response()->json([
                'message' => '2FA generated successfully',
                'qr_code_url' => url(Storage::url($filePath)),
                'secret_key' => $secretKey,
            ], 200);
        }
    }

    /**
     * Verify the 2FA code
     */
    public function verify2FACode(Request $request): JsonResponse
    {
        //        $otp = $request->input('otp');

        $request->validate([
            'otp' => 'required|regex:/^[0-9]{6}$/', // OTP must be a 6-digit number
            'user' => 'required|integer',
        ]);

        $google2fa = new Google2FA();

        // Retrieve the secret key from your storage
        $secretKey = User::where('id', $request->user)->first()->tfa_secret;
        $user = User::where('id', $request->user)->first();

        // Ensure that the secret key is a string
        $secretKey = (string) decrypt($secretKey);

        // Ensure that the OTP is a string
        $otp = (string) $request->otp;

        $isValid = $google2fa->verifyKey($secretKey, $otp);

        if ($isValid) {
            // OTP is valid. Generate the token.
            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;
            if (! $user->is_tfa) {
                $user->is_tfa = true;
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => '2FA enabled for the user',
                    'token' => $plainTextToken,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => '2FA code verified successfully',
                'token' => $plainTextToken,
            ], 200);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid 2FA code',
                'data' => null,
            ], 400);
        }
    }

    /**
     * Disable 2FA for a user
     */
    public function disable2FA(Request $request): JsonResponse
    {
        $request->validate([
            'user' => 'required|integer',
        ]);

        $user = User::where('id', $request->user)->first();
        $user->tfa_secret = null;
        $user->is_tfa = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => '2FA disabled successfully',
            'data' => null,
        ], 200);
    }

    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'password_hash' => 'required',
                'new_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'confirm_password' => 'required|same:new_password',
            ], [
                'new_password.required' => 'The new password field is required.',
                'new_password.min' => 'The new password must be at least 8 characters.',
                'new_password.regex' => 'The new password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'confirm_password.required' => 'The confirmation password field is required.',
                'confirm_password.same' => 'The confirmation password must match the new password.',
            ]);

            $user = User::where('otp_hash', $request->password_hash)->first();

            if (! $user) {

                return response()->json([
                    'status' => 'failure',
                    'message' => 'Otp doesnt exist',
                    'data' => null,
                ], 401);
            }

            $user->password = Hash::make($request->new_password);
            $user->otp_hash = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully',
                'data' => null,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'errors' => $e->validator->errors(),
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }
}
