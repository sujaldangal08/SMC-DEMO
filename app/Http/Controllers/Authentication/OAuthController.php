<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\User;

class OAuthController extends Controller
{
    /**
     * This method is used to handle the OAuth token received from the client.
     *
     * @param Request $request The incoming HTTP request containing the OAuth token.
     * @return JsonResponse Returns a JSON response indicating whether the authentication was successful or not.
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
        if (!$checkuser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
        else {
            // The user exists, authenticate them with Laravel Sanctum
            $tokenResult = $checkuser->createToken('api-token');
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addHours(1); // Token expires in 1 hour
            $token->save();

            $plainTextToken = $tokenResult->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated',
                'token' => $plainTextToken,
            ], 200);
        }
    }
}
