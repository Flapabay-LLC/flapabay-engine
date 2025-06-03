<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GetOTPEmail;
use App\Models\User;
use App\Traits\AuthTrait;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Twilio\Rest\Client;


class AuthenticatorController extends Controller
{

    use AuthTrait;

/**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,' . $request->phone . ',phone',
            'phone' => 'required|digits_between:7,15',
            'dob' => 'required|date',
            'password' => 'required|min:6', // Make sure password is included
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        // Step 2: Update or create the user based on the phone number
        $user = User::updateOrCreate(
            ['phone' => $request->phone], // search condition
            [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Hash the password
            ]
        );

        // Step 3: Create or update user details based on user_id
        UserDetail::updateOrCreate(
            ['user_id' => $user->id], // search condition
            [
                'phone' => $request->phone ?? null,
                'dob' => $request->dob ?? null,
            ]
        );

        // Step 4: Generate a JWT token
        $token = JWTAuth::fromUser($user);

        // Step 5: Return response
        return response()->json([
            'message' => 'User successfully registered!',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                'email' => 'required|email:dns|string',
                'password' => 'required|string|min:8',
            ]);

            // Check if user exists
            $user = User::orWhere('email', $request->email)
                        ->orWhere('phone', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials, user not found'
                ], 404);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials, password does not match'
                ], 401);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registerUserDetails(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,',
            'phone' => 'required|digits_between:7,15|unique:users,phone,',
            'dob' => 'required|date',
            'password' => 'required|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }
    
        // Step 2: Find or create/update user
        $user = User::where('phone', $request->phone)->where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Step 5: Check if user has complete records
        $requiredFields = ['fname', 'lname', 'email', 'phone', 'password'];
        $isProfileComplete = true;

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                $isProfileComplete = false;
                break;
            }
        }

        // Step 6: If profile is complete, authenticate and return token
        if ($isProfileComplete) {
            return response()->json([
                'success' => false,
                'message' => 'User already registered'
            ], 404);
        }

        // Update user
        $user->update([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


    
        // Step 3: Create or update user details
        UserDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone ?? null,
                'dob' => $request->dob ?? null,
            ]
        );
    
        // Step 4: Generate a JWT token
        $token = JWTAuth::fromUser($user);
    
        // Step 5: Return response
        return response()->json([
            'message' => 'User successfully updated!',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }
    

    /**
     * Handle the generation and sending of an OTP.
     */
    public function getPhoneOtp(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'code' => 'required', // Validate country code like +254, +1, +91
            'phone' => 'required|digits_between:7,15', // Validate phone digits only
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'code'=>400], 400);
        }

        // Step 2: Combine full phone in E.164 format
        $fullPhone = $request->code . $request->phone;

        // Step 3: Check if the user exists
        $user = User::where('phone', $fullPhone)->first();
        if (!$user) {
            $user = User::create([
                'phone' => $fullPhone,
                'password' => Hash::make('12345678'),
            ]);
        }

        $otp = $this->generate_otp($user, $request);

        // Step 6: Cache the OTP for quick retrieval
        Cache::put('otp_' . $fullPhone, $otp);

        // Step 7: Send OTP via Twilio
        try {
            // $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

            // $twilio->messages->create('+'.$fullPhone, [
            //     'from' => env('TWILIO_FROM'),
            //     'body' => "Your OTP is: $otp"
            // ]);

            return response()->json(['message' => 'OTP sent to your phone!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP. ' . $e->getMessage()], 500);
        }
    }


/**
     * Handle the generation and sending of an OTP.
     */
    public function getEmailOtp(Request $request)
    {

        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:dns|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Step 2: Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make('12345678'),
            ]);
        }

        // Step 3: Generate the OTP
        $otp = $this->generate_otp($user, $request);

        // Step 6: Store the OTP in the cache for quick retrieval
        Cache::put('otp_' . $request->email, $otp);

        // Step 7: Send the OTP via email
        Mail::to($request->email)->send(new GetOTPEmail($otp));

        // Step 5: Return a response
        return response()->json(['message' => 'OTP sent to your email!, Please check your mail'], 200);
    }

    public function verifyOtpByPhone(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'status' => false], 400);
        }
        
        // Step 2: Find user by phone
        $user = User::where('phone', '26'. $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found', 'status' => false], 404);
        }

        // Step 3: Check if OTP is expired
        if (Carbon::now()->format('Y-m-d H:i:s') > $user->otp_expires_at){
            return response()->json(['error' => 'OTP has expired', 'status' => false], 400);
        }

        // Step 4: Check if OTP matches
        if ($user->otp != $request->otp) {
            return response()->json(['error' => 'Invalid OTP', 'status' => false], 400);
        }

        // Step 5: Check if user has complete records
        $requiredFields = ['fname', 'lname', 'email', 'phone', 'password'];
        $isProfileComplete = true;

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                $isProfileComplete = false;
                break;
            }
        }

        // Step 6: If profile is complete, authenticate and return token
        if ($isProfileComplete) {
            $token = JWTAuth::fromUser($user);

            // Optionally mark OTP as verified
            $user->otp_verified_at = Carbon::now();
            $user->save();

            return response()->json([
                'message' => 'OTP verified and user authenticated!',
                'status' => true,
                'token' => $token,
                'user' => $user
            ], 200);
        }

        // Optionally mark OTP as verified
        $user->otp_verified_at = Carbon::now();
        $user->save();

        // Step 7: Return success without token if profile is incomplete
        return response()->json([
            'message' => 'OTP verified! Complete your profile to continue.',
            'status' => true
        ], 200);
    }

    public function verifyOtpByEmail(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:dns|string',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'status' => false], 400);
        }

        // Step 2: Find user by phone
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found', 'status' => false], 404);
        }

        // Step 3: Check if OTP is expired
        
        if (Carbon::now()->format('Y-m-d H:i:s') > $user->otp_expires_at) {
            return response()->json(['error' => 'OTP has expired', 'status' => false], 400);
        }
        
        // Step 4: Check if OTP matches
        if ($user->otp != $request->otp) {
            return response()->json(['error' => 'Invalid OTP', 'status' => false], 400);
        }

        // Step 5: Check if user has complete records
        $requiredFields = ['fname', 'lname', 'email', 'phone', 'password'];
        $isProfileComplete = true;

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                $isProfileComplete = false;
                break;
            }
        }

        // Step 6: If profile is complete, authenticate and return token
        if ($isProfileComplete) {
            $token = JWTAuth::fromUser($user);

            // Optionally mark OTP as verified
            $user->otp_verified_at = now();
            $user->save();

            return response()->json([
                'message' => 'OTP verified and user authenticated!',
                'status' => true,
                'token' => $token,
                'user' => $user
            ], 200);
        }

        // Optionally mark OTP as verified
        $user->otp_verified_at = now();
        $user->save();
        // Step 7: Return success without token if profile is incomplete
        return response()->json([
            'message' => 'OTP verified! Complete your profile to continue.',
            'status' => true
        ], 200);
    }


    /**
     * Handle forgot password functionality.
     */
    public function forgotPassword(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:dns|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $email = $request->input('email');

        // Step 2: Check if email exists in wp_users
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        // Step 3: Generate a unique token
        $token = Str::random(60);

        // Step 4: Store the token (consider using a password_resets table)
        // DB::table('password_resets')->insert([
        //     'email' => $email,
        //     'token' => $token,
        //     'created_at' => Carbon::now(),
        // ]);

        // Step 5: Send reset email
        $resetLink = url('/v1/reset-password?token=' . $token . '&email=' . urlencode($email));

        // Mail::send('emails.reset', ['link' => $resetLink], function ($message) use ($email) {
        //     $message->to($email);
        //     $message->subject('Password Reset Request');
        // });
        try {
            // Send the email
            Mail::send('emails.reset', ['link' => $resetLink], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Password Reset Request');
            });

            return response()->json(['success' => 'Password reset email has been resent.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email. Please try again later.'], 500);
        }
        // Step 6: Return response
        return response()->json(['message' => 'Reset email sent successfully'], 200);
    }


    /**
     * Handle the password reset process.
     */
    public function resetPassword(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:dns|string',
            'new_password' => 'required|string|min:8', // Ensure minimum length for security
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $email = $request->input('email');
        $newPassword = $request->input('new_password');

        // Step 3: Hash the new password
        $hashedPassword = Hash::make($newPassword);

        // Step 4: Update the password in wp_users table
        try {
            $user = User::where('email', $email)->update([
                'password' => $hashedPassword
            ]);

            // Step 5: Return a response
            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Password reset successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Validate that the token is provided in the request
            $this->validate($request, [
                'token' => 'required',
                'user_id' => 'required'
            ]);

            // Find the token in the database and revoke it
            $token = $request->user()->where('id', $request->user_id)->first();

            if ($token) {
                $token->revoke();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User  successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}