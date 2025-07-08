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
use App\Models\Listing;
use App\Models\Property;

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

        // dd($request);

        try {
            // Generate a unique 4-digit host_id
            do {
                $uuid = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            } while (\App\Models\User::where('host_id', $uuid)->exists());
            // Find the user by user_id
            $user = auth()->user();

            if ($user) {
                // Update the host_id field
                $user->host_id = $uuid;
                $user->save();

                // Handle image uploads (Wasabi/local) and save URLs to images JSON column
                $imagePaths = [];
                if ($request->hasFile('images')) {
                    $endpoint = 'https://s3.us-west-1.wasabisys.com';
                    $bucketName = 'flapapic';
                    $region = 'us-west-1';
                    $accessKey = 'HJG2GQM9QGBE4K6JCO2S';
                    $secretKey = 'HkHlBtvEszE2Uh18ZWgCw3t2BXd7CBPy75mMWEnD';

                    $s3Client = new \Aws\S3\S3Client([
                        'region'     => $region,
                        'version'    => 'latest',
                        'endpoint'   => $endpoint,
                        'credentials' => [
                            'key'    => $accessKey,
                            'secret' => $secretKey,
                        ],
                    ]);

                    foreach ($request->file('images') as $image) {
                        if ($image->isValid()) {
                            $fileName = time() . '_' . $image->getClientOriginalName();
                            $imageUrl = null;
                            try {
                                $result = $s3Client->putObject([
                                    'Bucket'     => $bucketName,
                                    'Key'        => 'properties/' . $fileName,
                                    'SourceFile' => $image->getPathname(),
                                ]);
                                if (isset($result['ObjectURL'])) {
                                    $imageUrl = $result['ObjectURL'];
                                } else {
                                    throw new \Exception('Object URL not returned from Wasabi');
                                }
                            } catch (\Exception $e) {
                                // Fallback to local storage
                                $localPath = $image->storeAs('properties', $fileName, 'public');
                                $imageUrl = \Storage::disk('public')->url($localPath);
                            }
                            $imagePaths[] = $imageUrl;
                        }
                    }
                }

                // Create Property for the host
                $propertyData = $request->only([
                    'title', 'description', 'location', 'address', 'country', 'latitude', 'longitude',
                    'check_in_hour', 'check_out_hour', 'num_of_guests', 'num_of_children', 'maximum_guests',
                    'allow_extra_guests', 'neighborhood_area', 'currency', 'price_range', 'price',
                    'price_per_night', 'additional_guest_price', 'children_price', 'amenities', 'house_rules',
                    'video_link', 'verified', 'num_of_bedrooms', 'num_of_bathrooms', 'num_of_quarters',
                    // Host fields
                    'type_of_place', 'address', 'coordinates',
                    'every_bedroom_has_lock', 'kind_of_bathrooms', 'who_is_there', 'favourites',
                    'safety_items', 'images', 'features', 'host_booking_settings',
                    'who_to_welcome_first_reservation', 'weekday_price', 'weekend_price', 'discounts', 'place_items',
                ]);
                // Map 'guests' to 'maximum_guests' if present
                if ($request->has('guests')) {
                    $propertyData['maximum_guests'] = $request->input('guests');
                }
                // Map 'bedrooms' to 'num_of_bedrooms' if present
                if ($request->has('bedrooms')) {
                    $propertyData['num_of_bedrooms'] = $request->input('bedrooms');
                }
                // Map 'bathrooms' to 'num_of_bathrooms' if present
                if ($request->has('bathrooms')) {
                    $propertyData['num_of_bathrooms'] = $request->input('bathrooms');
                }
                $propertyData['user_id'] = $user->id;
                if (!empty($imagePaths)) {
                    $propertyData['images'] = $imagePaths;
                }
                $property = Property::create($propertyData);

                // Create Listing for the property
                $listingData = [
                    'host_id' => $user->id,
                    'title' => $property->title,
                    'property_id' => $property->id,
                    'category_id' => $property->category_id ?? null,
                    'status' => true,
                    'published_at' => now(),
                    'cancellation_policy' => false,
                    'is_completed' => true,
                    'listing_type' => $request->input('listing_type', 'stay'),
                    'description' => $property->description,
                    'features' => $property->features,
                    'images' => $property->images,
                ];
                $listing = Listing::create($listingData);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Host registered and property/listing created successfully',
                    'data' => [
                        'user_id' => $user->id,
                        'host_id' => $user->host_id,
                        'property' => $property,
                        'listing' => $listing,
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

    /**
     * Complete user details with additional profile information
     *
     * @param Request $request
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeUserDetails(Request $request, $user_id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'my_interests' => 'nullable|array',
                'know_where_been' => 'nullable|string|max:255',
                'boi_title' => 'nullable|string|max:255',
                'am_obessed_with' => 'nullable|array',
                'most_useles_skill' => 'nullable|array',
                'spend_time_in' => 'nullable|array',
                'favourite_songs' => 'nullable|array',
                'shools_went_to' => 'nullable|array',
                'show_decade_born' => 'nullable|string|max:255',
                'pets' => 'nullable|array',
                'my_fun_fact' => 'nullable|string',
                'favourite_place' => 'nullable|string|max:255',
                'my_work' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the user
            $user = User::find($user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Find or create user details
            $userDetail = UserDetail::firstOrCreate(['user_id' => $user_id]);

            // Update user details
            $userDetail->update($request->all());

            // Update user's profile_complete status
            $user->profile_complete = true;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User details completed successfully',
                'data' => [
                    'user' => $user,
                    'user_details' => $userDetail
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
