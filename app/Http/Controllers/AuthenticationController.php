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
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

class AuthenticationController extends Controller
{
    /**
     * Login a user and return a token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'token' => 'nullable|string',
            ]);
            $user = User::where('email', $credentials['email'])->first();

            if (! $user) {
                return response()->json(['message' => 'Invalid Credentials1'], 401);
            }

            if ($user['login_attempts'] >= $user->role->max_login_attempts) {
                $user->deactivate();

                return response()->json(['message' => 'You account has been deactivated. Please contact your admin in order to activate it again'], 401);
            }
            // dd($user->role->max_login_attempts, $user['login_attempts']);
            $token = $credentials['token'];
            unset($credentials['token']);

            if (auth()->attempt($credentials)) {
                $user->resetLoginAttempts();
                $user->device_token = $token;
                $user->save();
                if ($user->role->role === 'customer' && $user->email_verified_at === null) {
                    return response()->json(['message' => 'Please verify your email'], 401);
                }
            } else {
                $user->incrementLoginAttempts();

                return response()->json(['message' => 'Invalid Credentials'], 401);
            }

            // Create a new token for the user
            $tokenResult = $user->createToken('authToken', ['*'], now()->addWeek());

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
                'message' => 'Invalid Credentials',
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
            //Send welcome mail upon registration
            $username = $request->name;

            $welcomeTemplate = \App\Models\EmailTemplate::where('template_type', 'welcome')->first();

            $subjectWelcome = $welcomeTemplate->subject; // Retrieve the subject from the emailTemplate model
            $welcome_type = $welcomeTemplate->template_type; // Retrieve the template type from the emailTemplate model

            $mailableWelcome = new EmailTemplate($username, $subjectWelcome, $welcome_type);
            Mail::to($user->email)->send($mailableWelcome);

            // Always generate a new OTP
            $otp = rand(100000, 999999);

            $payload = [
                'otp' => $otp,
                'attempt' => 5,
                'last_attempt' => null,
            ];
            $user->otp = Crypt::encryptString(json_encode($payload));

            // Set otp_expiry to be 5 minutes from now
            $user->otp_expiry = Carbon::now()->addMinutes(5);

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
                'message' => 'Validation error',
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'message' => 'Registration failed',
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

        // Decrypt the payload and access the 'otp' field
        $payload = json_decode(Crypt::decryptString($checkUser->otp), true);
        $otp = $payload['otp'];
        // Check if the current time is greater than or equal to the OTP expiry time
        if ($second >= $secondTwo) {
            // If the OTP has expired, return a JSON response with an error message
            return response()->json(['message' => 'OTP has expired'], 401);
        } elseif ($otp === (int) $request->otp) {

            // If the OTP provided in the request matches the OTP stored in the user record,
            $checkUser->email_verified_at = Carbon::now(); // set the email_verified_at field to the current time
            $checkUser->otp = null; // set the otp field to null
            $checkUser->otp_expiry = null; // set the otp_expiry field to null

            $otp_hash = Crypt::encryptString(Carbon::now()->toDateTimeString().'_'.Str::random(10));

            $checkUser->otp_hash = $otp_hash;

            $checkUser->save();

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully.',
                'hash' => $checkUser->otp_hash,
            ], 200);
        } else {
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
                'message' => 'User creation failed',
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
                'message' => 'Logout failed',
                'data' => null,
            ], 400);
        }
    }

    /**
     * Reset the user's password
     *
     * @return JsonResponse
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
     * This method handles the forgot password functionality.
     * It validates the email provided in the request, checks if the user exists,
     * and if the user has not exceeded the maximum number of OTP attempts.
     * If the user has exceeded the maximum number of attempts, it waits for 5 minutes before allowing another attempt.
     * If the user has not exceeded the maximum number of attempts, it generates a new OTP, sends it to the user's email,
     * and updates the user's record with the new OTP and the number of attempts.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();
            if ($user->otp == null) {
                $otp = rand(100000, 999999);

                $payload = [
                    'otp' => $otp,
                    'attempt' => 5,
                    'last_attempt' => null,
                ];
                $user->otp = Crypt::encryptString(json_encode($payload));
            }
            $decodeTime = json_decode(Crypt::decryptString($user->otp), true);
            $lastAttempt = strtotime($decodeTime['last_attempt']);
            $now = time();

            if (($now - $lastAttempt) < (5 * 60)) { // 5 minutes * 60 seconds
                $remainingTime = 5 - round(($now - $lastAttempt) / 60); // Convert the difference from seconds to minutes

                return response()->json([
                    'status' => 'failure',
                    'message' => 'You have exceeded the maximum number of attempts. Please wait for '.$remainingTime.' minutes.',
                    'data' => null,
                ], 429); // 429 Too Many Requests
            }

            if ($user->otp != null) {
                $decodedJson = json_decode(Crypt::decryptString($user->otp), true);
                $otp = $decodedJson['otp'];
                $attempt = $decodedJson['attempt'] - 1;
                $decodedJson['last_attempt'] = null;
                $payload = [
                    'otp' => $otp,
                    'attempt' => $attempt,
                    'last_attempt' => null,
                ];
                $user->otp = Crypt::encryptString(json_encode($decodedJson));
                $user->update();
            }
            $decode = json_decode(Crypt::decryptString($user->otp), true);
            if ($decode['attempt'] === 0) {
                $payload = [
                    'otp' => $otp,
                    'attempt' => 5,
                    'last_attempt' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
                ];
                $user->otp = Crypt::encryptString(json_encode($payload));
                $user->update();

                return response()->json([
                    'status' => 'failure',
                    'message' => 'You have exceeded the maximum number of attempts. Try again after 10 minutes.',
                    'data' => null,
                ], 401);
            }
            $user->otp = Crypt::encryptString(json_encode($payload));
            $decodedJson = json_decode(Crypt::decryptString($user->otp), true);
            $user->save();

            $emailTemplate = \App\Models\EmailTemplate::where('template_type', 'otp')->first(); // Replace 1 with the ID of the email template you want to fetch
            $subject = $emailTemplate->subject; // Retrieve the subject from the emailTemplate model
            $template_type = $emailTemplate->template_type; // Retrieve the template type from the emailTemplate model
            $username = $user->name;
            // Create a new instance of the mailable and pass the email template to it
            $mailable = new EmailTemplate($username, $subject, $template_type, $otp);

            // Send the email
            Mail::to($user->email)->send($mailable); // Replace 'recipient@example.com' with the recipient's email address

            return response()->json([
                'status' => 'success',
                'message' => 'Please check your email for the OTP to reset your password, you have '.$decodedJson['attempt'].' attempts left',
                'data' => null,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'errors' => $e->validator->errors(),
                'message' => 'Validation error',
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'message' => 'Failed to send OTP',
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
                'message' => 'Invalid credentials',
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
                'status' => 'success',
                'message' => '2FA generated successfully',
                'data' => [
                    'qr_code_url' => url(Storage::url($filePath)),
                    'secret_key' => $secretKey,
                ],
            ], 200);
        }
    }

    /**
     * This method is responsible for verifying the 2FA code provided by the user.
     * It first validates the request data, ensuring that the OTP is a 6-digit number and the user ID is provided.
     * Then, it creates a new instance of the Google2FA class.
     * It retrieves the user record that matches the provided user ID and decrypts the user's tfa_secret.
     * It ensures that the OTP and the secret key are strings.
     * It verifies the OTP against the secret key using the Google2FA class.
     * If the OTP is valid, it creates a new token for the user and sets the token's expiry time to 1 hour from now.
     * If the user's is_tfa field is false, it sets it to true and saves the user record.
     * Finally, it returns a JSON response indicating that the 2FA code was verified successfully and provides the token.
     * If the OTP is not valid, it returns a JSON response with an error message.
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function verify2FACode(Request $request): JsonResponse
    {
        // Validate the request data
        $request->validate([
            'otp' => 'required|regex:/^[0-9]{6}$/', // OTP must be a 6-digit number
            'user' => 'required|integer',
        ]);

        // Create a new instance of the Google2FA class
        $google2fa = new Google2FA();

        // Retrieve the user record that matches the provided user ID and decrypt the user's tfa_secret
        $secretKey = User::where('id', $request->user)->first()->tfa_secret;
        $user = User::where('id', $request->user)->first();

        // Ensure that the secret key is a string
        $secretKey = (string) decrypt($secretKey);

        // Ensure that the OTP is a string
        $otp = (string) $request->otp;

        // Verify the OTP against the secret key using the Google2FA class
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
     * This method is responsible for disabling two-factor authentication (2FA) for a user.
     * It first validates the request data, ensuring that the user ID is provided.
     * Then, it retrieves the user record that matches the provided user ID.
     * If the user is found, it sets the user's tfa_secret to null and is_tfa to false, and saves the user record.
     * Finally, it returns a JSON response indicating that 2FA was disabled successfully.
     */
    public function disable2FA(Request $request): JsonResponse
    {
        // Validate the request data
        $request->validate([
            'user' => 'required|integer',
        ]);

        // Retrieve the user record that matches the provided user ID
        $user = User::where('id', $request->user)->first();

        // Set the user's tfa_secret to null and is_tfa to false
        $user->tfa_secret = null;
        $user->is_tfa = false;

        // Save the user record
        $user->save();

        // Return a JSON response indicating that 2FA was disabled successfully
        return response()->json([
            'status' => 'success',
            'message' => '2FA disabled successfully',
            'data' => null,
        ], 200);
    }

    /**
     * This method is responsible for changing the user's password.
     * It first validates the request data, ensuring that the password_hash is provided,
     * and that the new password meets the necessary requirements (minimum length, includes uppercase and lowercase letters, a number, and a special character).
     * Then, it retrieves the user record that matches the provided password_hash.
     * If the user is not found, it returns a JSON response with an error message.
     * If the user is found, it hashes the new password, sets the user's password to the hashed password,
     * sets the user's otp_hash to null, and saves the user record.
     * Finally, it returns a JSON response indicating that the password was changed successfully.
     */
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
                'message' => 'Validation error',
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'exception' => $e->getMessage(),
                'message' => 'Failed to change password',
                'data' => null,
            ], 400);
        }
    }
}
