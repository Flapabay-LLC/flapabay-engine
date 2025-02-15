<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GetOTPEmail;
use App\Models\User;
use App\Models\UserDetail;
use App\Traits\MySMS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Infobip\Api\SendSmsApi;
use Infobip\Api\SmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsBulkRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Pnlinh\InfobipSms\Facades\InfobipSms;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticatorController extends Controller
{
    use MySMS;
    /**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        // Validate the request
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        // dd($request);
        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        // Create the user with hashed password
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password), // Hash the password
        ]);

        UserDetail::create([
            'user_id' => $user->id,
            'phone' => $request->phone ?? null,
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);
        // Return success response
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
                'email' => 'required|string|max:255',
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

    /**
     * Handle the generation and sending of an OTP.
     */
    public function getPhoneOtp(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|string',
            'phone' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Step 2: Check if the user exists
        $user = User::orWhere('email', $request->email)
            ->orWhere('phone', $request->phone)->first();

        if (!$user || !$user->phone) {
            return response()->json(['error' => 'User not found or no phone number registered'], 404);
        }

        // Step 3: Generate the OTP
        $otp = rand(100000, 999999);

        // Step 4: Set OTP expiration time
        $expiresAt = Carbon::now()->addMinutes(5);

        // Step 5: Store OTP in the database
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt
        ]);

        // Step 6: Store OTP in cache
        Cache::put('otp_' . $request->email, $otp, $expiresAt);

        // Step 7: Send OTP via Infobip SMS
        try {

            // Send the SMS message
            $smsResponse = $this->sendOTP($user, $otp);

            return response()->json([
                'message' => 'OTP sent to your phone! Please check your SMS.',
                'messageId' => $smsResponse->getMessages()[0]->getMessageId()
            ], 200);
        } catch (ApiException $apiException) {
            // Log the detailed error for debugging
            Log::error('Infobip SMS Error', [
                'error' => $apiException->getMessage(),
                'code' => $apiException->getCode(),
                'response' => $apiException->getResponseBody()
            ]);

            return response()->json([
                'error' => 'Failed to send OTP via SMS',
                'message' => 'Please try again or contact support if the problem persists'
            ], 500);
        }
    }

    /**
     * Handle the generation and sending of an OTP.
     */
    public function getEmailOtp(Request $request)
    {

        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Step 2: Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Step 3: Generate the OTP
        $otp = rand(100000, 999999); // Generate a 6-digit OTP

        // Step 4: Set OTP expiration time
        $expiresAt = Carbon::now()->addMinutes(5); // Set expiration time to 5 minutes

        // Step 5: Store the OTP and expiration time in the database
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();

        // Step 6: Store the OTP in the cache for quick retrieval
        Cache::put('otp_' . $request->email, $otp, $expiresAt);

        // Step 7: Send the OTP via email
        Mail::to($request->email)->send(new GetOTPEmail($otp));

        // Step 5: Return a response
        return response()->json(['message' => 'OTP sent to your email!, Please check your mail'], 200);
    }

    public function verifyOtp(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'status' => false], 400);
        }

        // Step 2: Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found', 'status' => false], 404);
        }

        // // Step 3: Check if OTP is expired after 5 minutes
        // Check if expired
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['error' => 'OTP has expired'], 400);
        }

        // Step 4: Check if OTP is correct
        if ($user->otp != $request->otp) {
            return response()->json(['error' => 'Invalid OTP', 'status' => false], 400);
        }

        // OTP is valid and not expired
        return response()->json(['message' => 'OTP verified successfully!', 'status' => true], 200);
    }

    /**
     * Handle forgot password functionality.
     */
    public function forgotPassword(Request $request)
    {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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
            'email' => 'required|email',
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
