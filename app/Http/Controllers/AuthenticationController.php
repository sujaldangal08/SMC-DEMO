<?php

namespace App\Http\Controllers;

use App\Mail\EmailTemplate;
use Illuminate\Http\Request;
use App\Models\User;
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

                $tokenResult = $request->user()->createToken('api-token');
                $token = $tokenResult->accessToken;
                $token->expires_at = now()->addHours(1); // Token expires in 1 hour
                $token->save();

                $plainTextToken = $tokenResult->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $plainTextToken,
                ]);
            }else {
                dd(auth()->getLastAttempted());
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
            $user->otp =$otp;

            // Set otp_expiry to be 5 minutes from now
            $user->otp_expiry = Carbon::now()->addMinutes(5);
            $username = $request->name;

            $welcomeTemplate = \App\Models\EmailTemplate::where('template_type', 'welcome')->first();
            if (!$welcomeTemplate) {
                return response()->json(['message' => 'Welcome Email Template not found'], 404);
            }
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
        } elseif($checkUser->otp === $request->otp) {
            // If the OTP provided in the request matches the OTP stored in the user record,
            $checkUser->email_verified_at = Carbon::now();// set the email_verified_at field to the current time
            $checkUser->otp = null;// set the otp field to null
            // $tokenResult = $user->createToken('api-token');
            // $token = $tokenResult->accessToken;

            // $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            // $token->save();

            // $plainTextToken = $tokenResult->plainTextToken;


            $checkUser->save();


                return response()->json(['message' => 'OTP verified successfully']);

        //     return response()->json(['message' => 'Account created successfully', 'token' => $plainTextToken], 201);
        // } catch (ValidationException $e) {
        //     return response()->json(['error' => $e->validator->errors()->getMessages()], 401);
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

    /**
     * This method is used to handle the forgot password request.
     *
     * @param Request $request The incoming HTTP request containing the user's email.
     * @return JsonResponse Returns a JSON response indicating whether the password reset email was sent successfully or not.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'email' => 'required|email'
            ]);

            // Fetch the user record based on the email provided in the request
            $user = User::where('email', $request->email)->first();

            // Check if the user exists
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Generate a random OTP
            $otp = rand(100000, 999999);

            // Store the OTP and its expiry time in the user record
            $user->otp = $otp;
            $user->otp_expiry = Carbon::now()->addMinutes(5);
            $user->save();

            // Fetch the user's name
            $username = $user->name;

            // Fetch the email template for the OTP
            $emailTemplate = \App\Models\EmailTemplate::where('template_type', 'otp')->first();
            $subject = $emailTemplate->subject;
            $template_type = $emailTemplate->template_type;

            // Create a new instance of the mailable and pass the email template to it
            $mailable = new EmailTemplate($username, $subject, $template_type, $otp);

            // Send the email with the OTP
            Mail::to($user->email)->send($mailable);

            // Return a success message
            return response()->json(['message' => 'OTP sent to your email']);
        } catch (\Exception $e) {
            // If an exception occurs, return the exception message
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

    /**
     * This method is used to generate a Two-Factor Authentication (2FA) secret key for a user and a QR code that the user can scan to add the 2FA to their authentication app.
     *
     * @param int $userID The ID of the user for whom the 2FA secret key and QR code are to be generated.
     * @return JsonResponse Returns a JSON response containing the URL of the generated QR code.
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function twoFactorGenerate($userID): JsonResponse
    {
        // Fetch the user record based on the user ID provided
        $user = User::where('id', $userID)->first();

        // Check if the user already has a 2FA secret key
        $check2fa = $user->tfa_secret;

        if($check2fa){
            // If the user already has a 2FA secret key, return a JSON response with a message indicating that 2FA is already enabled
            return response()->json(['message' => '2FA already enabled']);
        }else{
            // If the user does not have a 2FA secret key, generate a new one
            $google2fa = new Google2FA();
            $companyName = env('APP_NAME');
            $companyEmail = 'nujan@shotcoder.com';
            $secretKey = $google2fa->generateSecretKey();

            // Save the secret key to the user's record
            $user->tfa_secret = $secretKey;
            $user->save();

            // Generate a QR code URL using the company name, company email, and secret key
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                $companyName,
                $companyEmail,
                $secretKey
            );

            // Create a new SVG image renderer and writer
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);

            // Generate the QR code as a string
            $qrCode = $writer->writeString($qrCodeUrl);

            // Define the file path for the QR code
            $filePath = 'qrcodes/' . Str::random(10) . '.svg';

            // Check if the 'qrcodes' directory exists and create it if it doesn't
            if (!Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }

            // Save the QR code to a file in the public directory
            Storage::disk('public')->put($filePath, $qrCode);

            // Return a JSON response with the URL of the QR code
            return response()->json(['qr_code_url' => url(Storage::url($filePath))]);
        }
    }

    /**
     * This method is used to verify the Two-Factor Authentication (2FA) code entered by the user.
     *
     * @param Request $request The incoming HTTP request containing the user's ID and the 2FA code.
     * @return JsonResponse Returns a JSON response indicating whether the 2FA code verification was successful or not.
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function verify2FACode(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'otp' => 'required|numeric', // The 2FA code must be provided and must be a number
            'user' => 'required|integer', // The user's ID must be provided and must be an integer
        ]);

        // Create a new instance of the Google2FA class
        $google2fa = new Google2FA();

        // Retrieve the 2FA secret key from the user record in the database
        $secretKey = User::where('id',  $request->user)->first()->tfa_secret;

        // Ensure that the secret key is a string
        $secretKey = (string) $secretKey;

        // Ensure that the 2FA code is a string
        $otp = (string) $request->otp;

        // Verify the 2FA code using the secret key
        $isValid = $google2fa->verifyKey($secretKey, $otp);

        if ($isValid) {
            // If the 2FA code is valid, return a success message
            return response()->json(['message' => '2FA code verified successfully']);
        } else {
            // If the 2FA code is not valid, return an error message
            return response()->json(['message' => 'Invalid 2FA code'], 400);
        }
    }

}
