<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OAuthController extends Controller
{
    /**
     * This method is used to handle the OAuth token received from the client.
     *
     * @param  Request  $request  The incoming HTTP request containing the OAuth token.
     * @return JsonResponse Returns a JSON response indicating whether the authentication was successful or not.
     *
     * @throws GuzzleException
     */
    public function OAuthReceive(Request $request)
    {
        // Get the token from the request
        $token = $request->token;

        // Create a new Guzzle HTTP client
        $client = new Client();

        // Send a GET request to the Google OAuth2 API to validate the token
        $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
            'query' => [
                'id_token' => $token,
            ],
        ]);

        // Decode the response body
        $data = json_decode($response->getBody(), true);

        // Get the email from the response data
        $email = $data['email'];

        // Check if a user with this email exists in the database
        $checkuser = User::where('email', $email)->first();

        // If the user doesn't exist, return an error response
        return $this->extracted($checkuser);
    }

    /**
     * This method is used to handle the OAuth token received from the Facebook client.
     *
     * @param  Request  $request  The incoming HTTP request containing the OAuth token.
     * @return JsonResponse Returns a JSON response indicating whether the authentication was successful or not.
     *
     * @throws ConnectionException
     */
    public function facebookOauthReceive(Request $request)
    {
        // Get the token from the request
        $accessToken = $token = $request->token;

        $userDataUrl = 'https://graph.facebook.com/v12.0/me?fields=id,name,email';
        $userDataResponse = Http::withToken($accessToken)->get($userDataUrl);
        $userData = $userDataResponse->json();

        $checkuser = User::where('email', $userData['email'])->first();

        // If the user doesn't exist, return an error response
        return $this->extracted($checkuser);

    }

    /**
     * This method is used to handle the user authentication process.
     * It first checks if the user exists in the database.
     * If the user doesn't exist, it returns a JSON response with a status of 'failure' and a message of 'Unauthorized'.
     * If the user does exist, it authenticates them with Laravel Sanctum and generates a token for them.
     * The token's expiry time is set to 1 hour from the current time.
     * Finally, it returns a JSON response with a status of 'success', a message of 'Authenticated', and the user data and token information.
     *
     * @param  User  $checkuser  The user object retrieved from the database.
     * @return JsonResponse Returns a JSON response indicating whether the authentication was successful or not.
     */
    public function extracted(User $checkuser): JsonResponse
    {
        if (! $checkuser) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        } else {
            // The user exists, authenticate them with Laravel Sanctum
            $tokenResult = $checkuser->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated',
                'data' => $checkuser,
                'access_token' => $plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at,
            ], 200);
        }
    }
}
