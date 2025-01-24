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
}
