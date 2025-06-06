<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class FacebookController extends Controller
{
    // Redirect to Facebook for authentication
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    // Handle the callback from Facebook
    public function handleFacebookCallback(Request $request)
    {
        try {
            // Get user info from Facebook
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            // Find or create the user in the database
            $user = User::firstOrCreate(
                ['facebook_id' => $facebookUser->id],
                [
                    'fname' => $facebookUser->name,
                    'email' => $facebookUser->email, // Ensure email is not null
                    'password' => Hash::make(uniqid()), // Random password
                ]
            );

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function facebookSignIn(Request $request)
    {
        $validatedData = $request->validate([
            'fb_token' => 'required|string',
        ]);

        try {
            // Get Facebook user details using token
            $fbUser = Socialite::driver('facebook')->stateless()->userFromToken($validatedData['fb_token']);

            // Optional: Split name into fname and lname
            $fullName = $fbUser->getName();
            $names = explode(' ', $fullName, 2);
            $fname = $names[0];
            $lname = $names[1] ?? '';

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $fbUser->getEmail()],
                [
                    'fname' => $fname,
                    'lname' => $lname,
                    'email' => $fbUser->getEmail(),
                    'facebook_id' => $fbUser->getId(),
                    'password' => Hash::make(uniqid()), // Random secure password
                ]
            );

            // Generate JWT token
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'success' => true,
                'message' => 'Facebook sign-in successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Facebook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
