<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

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
                    'name' => $googleUser->getName(),
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
    public function googleSignIn(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'google_token' => 'required|string',
        ]);

        try {
            // Get Google user details
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($validatedData['google_token']);

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'fname' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' =>  Hash::make(uniqid()), // Random password
                ]
            );

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Google sign-in successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Google',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
