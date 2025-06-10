<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function index(Request $request)
    {
        // Placeholder for the index method logic
    }

    public function test()
    {
        try {
            // Query to fetch all users from the wp_users table
            $users = DB::table('users')->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($user_id){
        try {
            $id = $user_id;
            // Find the user by ID from the wp_users table
            $user = User::with('details')->where('id', $id)->first();

            // Check if user exists
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user details and return the updated user data.
     */
    public function update(Request $request, $user_id)
    {

        try {
            // Validate request data
            $validatedData = Validator::make($request->all(), [
                // These are in User Model
                'email' => 'nullable|email',
                'fname' => 'nullable|string|max:255',
                'lname' => 'nullable|string|max:255',

                // These are in UserDetail Model
                'bio' => 'nullable|string',
                'live_in' => 'nullable|string',
                'paypal_email' => 'nullable|email',
                'phone' => 'nullable|string|max:15',
                'website' => 'nullable|url',
                'skype' => 'nullable|url',
                'facebook' => 'nullable|url',
                'twitter' => 'nullable|url',
                'linkedin' => 'nullable|url',
                'pinterest' => 'nullable|url',
                'youtube' => 'nullable|url',
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validatedData->errors()
                ], 422);
            }

            // Extract validated data
            $data = $validatedData->validated();

            // Find the user by ID
            $user = User::with('details')->findOrFail($user_id);

            // Update User model
            $user->update($data);

            // Update UserDetail model if it exists
            $userDetail = UserDetail::where('user_id', $user_id)->first();
            if ($userDetail) {
                $userDetail->update($data);
            } else {
                // Optionally, create a new UserDetail if it doesn't exist
                $userDetail = UserDetail::create(array_merge($data, ['user_id' => $user_id]));
            }

            // Return the updated user data
            return response()->json([
                'success' => true,
                'message' => 'User  updated successfully',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfilePicture(Request $request, $user_id)
    {
        try {
            // Validate the incoming request
            $validatedData = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            ]);

            // Check if validation fails
            if ($validatedData->fails()) {
                return response()->json(['error' => $validatedData->errors()], 400);
            }

            // Retrieve the uploaded file
            $file = $request->file('profile_picture');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $fileUrl = null;
            $wasabiUploadSuccess = false;

            try {
                // Define your Wasabi S3 endpoint and credentials
                $endpoint = 'https://s3.us-west-1.wasabisys.com';
                $bucketName = 'flapapic';
                $region = 'us-west-1';
                $accessKey = 'HJG2GQM9QGBE4K6JCO2S';
                $secretKey = 'HkHlBtvEszE2Uh18ZWgCw3t2BXd7CBPy75mMWEnD';

                // Create an S3 client with the specified configuration for Wasabi
                $s3Client = new S3Client([
                    'region'     => $region,
                    'version'    => 'latest',
                    'endpoint'   => $endpoint,
                    'credentials' => [
                        'key'    => $accessKey,
                        'secret' => $secretKey,
                    ],
                ]);

                // Attempt to upload the local file to the Wasabi bucket
                $result = $s3Client->putObject([
                    'Bucket'     => $bucketName,
                    'Key'        => $fileName,
                    'SourceFile' => $file->getRealPath(),
                ]);

                if (isset($result['ObjectURL'])) {
                    $fileUrl = $result['ObjectURL'];
                    $wasabiUploadSuccess = true;
                }
            } catch (\Exception $wasabiError) {
                Log::error('Wasabi upload failed: ' . $wasabiError->getMessage());
                $wasabiUploadSuccess = false;
            }

            // If Wasabi upload failed, fall back to local storage
            if (!$wasabiUploadSuccess) {
                try {
                    // Store in Laravel's public storage
                    $path = $file->storeAs('public/profile-pictures', $fileName);
                    $fileUrl = asset('storage/profile-pictures/' . $fileName);
                } catch (\Exception $localStorageError) {
                    Log::error('Local storage upload failed: ' . $localStorageError->getMessage());
                    throw new \Exception('Failed to upload profile picture to both Wasabi and local storage');
                }
            }

            // Update the user's profile picture URL in the database
            $user = UserDetail::where('user_id', $user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Save the file URL to the user's profile
            $user->profile_picture_url = $fileUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.',
                'file_url' => $fileUrl,
                'storage_type' => $wasabiUploadSuccess ? 'wasabi' : 'local'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Failed to update profile picture: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerHost(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'user_id' => 'required', // Ensure user_id exists in the users table
        ]);

        // dd('here');
        try {
            // Generate a UUID
            $uuid = random_int(1000, 9999);
            // Find the user by user_id
            $user = User::where('id',$request->input('user_id'))->first();

            if ($user) {
                // Update the host_id field
                $user->host_id = $uuid;
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Host registered successfully',
                    'data' => [
                        'user_id' => $user->id,
                        'host_id' => $user->host_id,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register host',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
