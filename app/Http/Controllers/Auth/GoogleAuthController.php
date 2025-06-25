<?php



namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;



use Illuminate\Support\Facades\Log;



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



public function googleSignIn(Request $request)
{
Log::info('🔐 Google Sign-In Request Received: ' . json_encode($request->all()));



$validatedData = $request->validate([
'googleToken' => 'required|string',
]);



try {
// Manually validate token using Google's tokeninfo endpoint
$response = Http::get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
'access_token' => $validatedData['googleToken']
]);



if (!$response->ok()) {
Log::error('❌ Invalid Google token response', ['response' => $response->body()]);
return response()->json([
'success' => false,
'message' => 'Invalid Google token',
'error' => 'Token verification failed with Google',
], 400);
}



$googleUser = $response->json();



Log::info('✅ Google user data from tokeninfo:', $googleUser);



// Create/find user
$user = User::firstOrCreate(
['email' => $googleUser['email']],
[
'fname' => $googleUser['email'],
'lname' => $googleUser['email'],
'google_id' => $googleUser['sub'],
'password' => Hash::make(uniqid()), // Random password
]
);



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
Log::error('❌ Google Sign-In Failed:', ['error' => $e->getMessage()]);



return response()->json([
'success' => false,
'message' => 'Failed to authenticate with Google',
'error' => $e->getMessage(),
], 500);
}
}
}

/app/Http/Controllers/Auth/GoogleAuthController.php
