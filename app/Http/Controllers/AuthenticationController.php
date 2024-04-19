<?php

namespace App\Http\Controllers;

use App\Mail\EmailTemplate;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Backend;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Role;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Carbon\Carbon;

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
                return response()->json(['message' => 'You account has been deactivated. Please contact your admin in order to activate it again'], 401);
            }

            if (auth()->attempt($credentials)) {
                $user->resetLoginAttempts();
                if ($user->role->role === 'customer' && $user->email_verified_at === null) {
                    return response()->json(['message' => 'Please verify your email'], 401);
                }
                if ($user->role->role === 'customer' && $user->tfa_secret === null) {
                    return response()->json(['message' => 'Please enable 2FA'], 401);
                }

                return response()->json([
                    'message' => 'Please verify your 2FA code',
                    'redirect' => '/verifyfa',
                    'user_id' => $user->id,  // Pass user id to use in the next request
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
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'confirm_password' => 'required|same:password'
            ],[
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

            return response()->json(['message' => 'Account created successfully, please check your email for the OTP'], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        }catch (\Exception $e) {
            return response()->json(['exception' => $e->getMessage()], 400);
        }



    }

    public function verifyOtp(Request $request): JsonResponse
    {
        // Fetch the user record based on the email provided in the request
        $checkUser = User::where('email', $request->email)->first();

        // Get the current time and convert it to a Unix timestamp
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $second = strtotime($now);

        // Convert the OTP expiry time to a Unix timestamp
        $secondTwo = strtotime($checkUser->otp_expiry);

        // Check if the current time is greater than or equal to the OTP expiry time
        if($second >= $secondTwo){
            // If the OTP has expired, return a JSON response with an error message
            return response()->json(['message' => 'OTP has expired'], 401);
        } elseif(Crypt::decryptString($checkUser->otp) === $request->otp) {
            // If the OTP provided in the request matches the OTP stored in the user record,
            $checkUser->email_verified_at = Carbon::now();// set the email_verified_at field to the current time
            $checkUser->otp = null;// set the otp field to null


            $checkUser->save();
            return response()->json(['message' => 'OTP verified successfully, you are now registered. Please login to continue']);
        } else {
            // If the OTP provided in the request does not match the OTP stored in the user record,
            // return a JSON response with an error message
            return response()->json(['message' => 'Invalid OTP'], 401);
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
                'email' => 'required|email'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
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

            return response()->json(['message' => 'OTP sent to your email']);
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

    public function verify2FACode(User &$user, Request $request): JsonResponse
    {
        $otp = $request->input('otp');
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
            // OTP is valid. Generate the token.
            $user = User::find($request->user);
            $tokenResult = $user->createToken('api-token');
            $token = $tokenResult->token;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json([
                'message' => '2FA code verified successfully',
                'token' => $plainTextToken,
            ]);
        } else {
            return response()->json(['message' => 'Invalid 2FA code'], 400);
        }

    }

}
