<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Google_Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google Callback.
     */
    public function googleCallback(Request $request)
    {
        try {
            // Get Google user details
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'fname' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(uniqid()), // Random password
                ]
            );

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to handle Google callback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Direct Google Sign-In using a token.
     */
    public function googleSignIn(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request
            $request->validate([
                'id_token' => 'required|string',
            ]);

            $idToken = $request->input('id_token');

            // Verify token with Google using HTTP client
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken
            ]);

            if (!$response->successful()) {
                Log::warning('Invalid Google ID token provided', ['token' => substr($idToken, 0, 20) . '...', 'ip' => $request->ip()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], 401);
            }

            $payload = $response->json();

            // Verify the token is for our application
            if ($payload['aud'] !== config('services.google.client_id')) {
                Log::warning('Google ID token audience mismatch', ['expected' => config('services.google.client_id'), 'received' => $payload['aud'], 'ip' => $request->ip()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token audience',
                ], 401);
            }

            // Extract user information from verified payload
            $googleUserId = $payload['sub'];
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? null;
            $emailVerified = $payload['email_verified'] ?? false;

            // Check if required fields are present
            if (!$googleUserId || !$email) {
                Log::error('Google token payload missing required fields', ['payload' => $payload, 'ip' => $request->ip()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token payload lacks required fields',
                ], 400);
            }

            // Check if email is verified
            if (!$emailVerified) {
                Log::warning('Unverified Google email attempt', ['email' => $email, 'ip' => $request->ip()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Google email not verified'
                ], 400);
            }

            // Optional: Domain restrictions (uncomment and modify as needed)
            // $allowedDomains = ['example.com', 'company.com'];
            // $emailDomain = substr(strrchr($email, '@'), 1);
            // if (!in_array($emailDomain, $allowedDomains)) {
            //     Log::warning('Domain restriction violation', ['email' => $email, 'domain' => $emailDomain, 'ip' => $request->ip()]);
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Email domain not allowed'
            //     ], 403);
            // }

            // Handle user in database
            $user = User::where('google_id', $googleUserId)->first();

            if (!$user) {
                // Check if user exists with same email
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    // Link Google account to existing user
                    $existingUser->update(['google_id' => $googleUserId]);
                    $user = $existingUser;
                    Log::info('Google account linked to existing user', ['user_id' => $user->id, 'email' => $email, 'ip' => $request->ip()]);
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'google_id' => $googleUserId,
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(), // Google emails are pre-verified
                    ]);
                    Log::info('New user registered via Google', ['user_id' => $user->id, 'email' => $email, 'ip' => $request->ip()]);
                }
            } else {
                // Update last login timestamp
                $user->touch();
                Log::info('User signed in via Google', ['user_id' => $user->id, 'email' => $user->email, 'ip' => $request->ip()]);
            }

            // Generate app-level session (JWT token)
            $token = JWTAuth::fromUser($user);

            Log::info('Google authentication successful', ['user_id' => $user->id, 'email' => $user->email, 'ip' => $request->ip()]);

            // Respond to frontend
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'is_host' => $user->isHost(),
                ],
                'token' => $token,
                'message' => 'Google authentication successful',
            ]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Google API request error during authentication', ['error' => $e->getMessage(), 'ip' => $request->ip(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Token verification failed',
            ], 401);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Google auth validation failed', ['errors' => $e->errors(), 'ip' => $request->ip()]);
            return response()->json([
                'success' => false,
                'message' => 'Token is missing',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Unexpected error during Google authentication', ['error' => $e->getMessage(), 'ip' => $request->ip(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
            ], 500);
        }
    }

    /**
     * Logout user by invalidating JWT token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Get the token from the request
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided',
                ], 400);
            }

            // Invalidate the token
            JWTAuth::invalidate($token);

            Log::info('User logged out successfully');

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);

        } catch (\Exception $e) {
            Log::error('Error during logout', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }
}
