<?php

namespace App\Http\Controllers;


use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Property;
use App\Models\UserReview;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * Get a list of properties without filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProperties(Request $request)
    {
        try {
            // Get the page number from the request, default to 1
            $page = $request->input('page', 1);

            // Paginate the properties with 10 items per page
            $properties = Property::paginate(10, ['*'], 'page', $page);

            // Return success response with paginated data
            return response()->json([
                'success' => true,
                'data' => $properties->items(),
                'total_results' => $properties->total(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch properties',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Create a new property and store it in wp_posts and wp_postmeta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProperties(StorePropertyRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Step 1: Create property record
            $property = Property::createProperty($validatedData);

            // Step 2: Handle image uploads
            $imagePaths = [];

            if ($request->hasFile('images')) {
                // Setup Wasabi S3 client
                $endpoint = 'https://s3.us-west-1.wasabisys.com';
                $bucketName = 'flapapic';
                $region = 'us-west-1';
                $accessKey = 'HJG2GQM9QGBE4K6JCO2S';
                $secretKey = 'HkHlBtvEszE2Uh18ZWgCw3t2BXd7CBPy75mMWEnD';

                $s3Client = new S3Client([
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
                        $wasabiUrl = null;

                        try {
                            $result = $s3Client->putObject([
                                'Bucket'     => $bucketName,
                                'Key'        => 'properties/' . $fileName,
                                'SourceFile' => $image->getPathname(),
                            ]);

                            if (isset($result['ObjectURL'])) {
                                $wasabiUrl = $result['ObjectURL'];
                            } else {
                                throw new Exception('Object URL not returned from Wasabi');
                            }
                        } catch (Exception $e) {
                            // Fallback to local storage
                            $localPath = $image->storeAs('properties', $fileName, 'public');
                            $wasabiUrl = Storage::disk('public')->url($localPath);
                        }

                        $imagePaths[] = $wasabiUrl;
                    }
                }
            }

            // Save image paths to the property
            if (!empty($imagePaths)) {
                $property->images = json_encode($imagePaths);
                $property->save();
            }
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => 'Property created successfully',
                "property" => $property,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "message" => 'Failed to create property',
                "error" => $e->getMessage(),
            ], 500);
        }
    }


    public function updateProperties(UpdatePropertyRequest $request) {
        // Step 1: Validate incoming request data
       

        try {
            DB::beginTransaction();
    
            // Fetch existing property
            $property = Property::find($request->input('property_id'));
            if (!$property) {
                return response()->json([
                    "success" => false,
                    "message" => 'Property not found.',
                ], 404);
            }
    
            // Update property data
            $property->update($request->validated());
    
            $imagePaths = [];
    
            if ($request->hasFile('images')) {
                $endpoint = 'https://s3.us-west-1.wasabisys.com';
                $bucketName = 'flapapic';
                $region = 'us-west-1';
                $accessKey = 'HJG2GQM9QGBE4K6JCO2S';
                $secretKey = 'HkHlBtvEszE2Uh18ZWgCw3t2BXd7CBPy75mMWEnD';
    
                $s3Client = new S3Client([
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
    
                        try {
                            $result = $s3Client->putObject([
                                'Bucket'     => $bucketName,
                                'Key'        => 'properties/images/' . $fileName,
                                'SourceFile' => $image->getPathname(),
                            ]);
    
                            if (isset($result['ObjectURL'])) {
                                $imagePaths[] = $result['ObjectURL'];
                            } else {
                                throw new \Exception('Wasabi upload returned no ObjectURL');
                            }
    
                        } catch (\Exception $e) {
                            Log::error('Wasabi upload failed: ' . $e->getMessage());
    
                            // Fallback: Store in local Laravel storage
                            $path = $image->store('properties/images', 'public');
                            if ($path) {
                                $imagePaths[] = Storage::disk('public')->url($path);
                            } else {
                                Log::error('Fallback local upload also failed');
                                throw new \Exception('Failed to upload image to Wasabi or local.');
                            }
                        }
                    }
                }
            }
    
            if (!empty($imagePaths)) {
                $property->images = json_encode($imagePaths);
                $property->save();
            }
    
            DB::commit();
    
            return response()->json([
                "success" => true,
                "message" => 'Property updated successfully',
                "property" => $property,
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => 'Failed to update property',
                "error" => $e->getMessage(),
            ], 500);
        }

    }



    public function deleteProperty($propertyId) {
        // Step 1: Validate the property ID
        if (!is_numeric($propertyId) || $propertyId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid property ID',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Step 2: Find the property
            $property = Property::findOrFail($propertyId);

            // Step 3: Delete related Listings
            Listing::where('property_id', $propertyId)->delete();

            // Step 4: Delete related Availability
            Availability::where('property_id', $propertyId)->delete();

            // Step 5: Delete related Bookings
            Booking::where('property_id', $propertyId)->delete();

            // Step 6: Delete the Property
            $property->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function getProperty($propertyId) {
        try {
            // Step 1: Validate the property ID
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property ID',
                ], 400);
            }

            // Step 2: Retrieve the property with its relationships
            $property = Property::with([
                'category' => function($query) {
                    $query->select('id', 'name', 'description');
                },
                'propertyType' => function($query) {
                    $query->select('id', 'name', 'description');
                },
                'reviews' => function($query) {
                    $query->select('id', 'property_id', 'user_id', 'rating', 'review', 'created_at')
                          ->with(['user' => function($q) {
                              $q->select('id', 'fname', 'lname', 'email');
                          }]);
                }
            ])
            ->select([
                'id', 'title', 'description', 'location', 'address', 'county', 'country',
                'latitude', 'longitude', 'check_in_hour', 'check_out_hour',
                'num_of_guests', 'num_of_children', 'maximum_guests',
                'allow_extra_guests', 'neighborhood_area', 'show_contact_form_instead_of_booking',
                'allow_instant_booking', 'currency', 'price_range', 'price',
                'price_per_night', 'additional_guest_price', 'children_price',
                'amenities', 'house_rules', 'page', 'rating', 'favorite',
                'images', 'video_link', 'verified', 'property_type_id'
            ])
            ->find($propertyId);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            // Step 3: Format the response data
            $propertyData = $property->toArray();
            
            // Ensure images are properly decoded if they're stored as JSON
            if (isset($propertyData['images']) && is_string($propertyData['images'])) {
                $propertyData['images'] = json_decode($propertyData['images'], true) ?? [];
            }

            // Ensure amenities are properly decoded if they're stored as JSON
            if (isset($propertyData['amenities']) && is_string($propertyData['amenities'])) {
                $propertyData['amenities'] = json_decode($propertyData['amenities'], true) ?? [];
            }

            // Ensure house_rules are properly decoded if they're stored as JSON
            if (isset($propertyData['house_rules']) && is_string($propertyData['house_rules'])) {
                $propertyData['house_rules'] = json_decode($propertyData['house_rules'], true) ?? [];
            }

            // Ensure price_range is properly decoded if it's stored as JSON
            if (isset($propertyData['price_range']) && is_string($propertyData['price_range'])) {
                $propertyData['price_range'] = json_decode($propertyData['price_range'], true) ?? [];
            }

            // Calculate average rating from reviews
            if (isset($propertyData['reviews']) && !empty($propertyData['reviews'])) {
                $propertyData['average_rating'] = collect($propertyData['reviews'])->avg('rating');
                $propertyData['total_reviews'] = count($propertyData['reviews']);
            } else {
                $propertyData['average_rating'] = 0;
                $propertyData['total_reviews'] = 0;
            }

            return response()->json([
                'success' => true,
                'message' => 'Property retrieved successfully',
                'property' => $propertyData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Property retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getPropertyReviews($propertyId) {
        // Step 1: Validate the property ID
        if (!is_numeric($propertyId) || $propertyId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid property ID',
            ], 400);
        }

        try {
            // Step 2: Retrieve reviews from UserReview Model where property_id = $propertyId
            $reviews = UserReview::where('property_id', $propertyId)->get();

            // Step 3: Check if reviews exist
            if ($reviews->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No reviews found for this property',
                    'reviews' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reviews retrieved successfully',
                'reviews' => $reviews,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getPropertyDescription($propertyId) {
        try {
            // Step 1: Validate the property ID
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property ID',
                ], 400);
            }

            // Step 2: Retrieve the property description
            $property = Property::select(['id', 'title', 'description', 'about_place'])
                ->find($propertyId);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Property description retrieved successfully',
                'data' => [
                    'title' => $property->title,
                    'description' => $property->description,
                    'about_place' => $property->about_place
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Property description retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property description',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPropertyPriceDetails($propertyId) {
        try {
            // Step 1: Validate the property ID
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property ID',
                ], 400);
            }

            // Step 2: Retrieve the property price details
            $property = Property::select([
                'id',
                'title',
                'currency',
                'price',
                'price_per_night',
                'price_range',
                'additional_guest_price',
                'children_price'
            ])->find($propertyId);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            // Format price range if it exists
            $priceRange = $property->price_range ? json_decode($property->price_range, true) : null;

            return response()->json([
                'success' => true,
                'message' => 'Property price details retrieved successfully',
                'data' => [
                    'title' => $property->title,
                    'currency' => $property->currency,
                    'price' => $property->price,
                    'price_per_night' => $property->price_per_night,
                    'price_range' => $priceRange,
                    'additional_guest_price' => $property->additional_guest_price,
                    'children_price' => $property->children_price
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Property price details retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property price details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getPropertyAmenities($propertyId) {
        try {
            // Step 1: Validate the property ID
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property ID',
                ], 400);
            }

            // Step 2: Retrieve the property amenities
            $property = Property::select(['id', 'title', 'amenities'])
                ->find($propertyId);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            // Decode amenities JSON if it exists
            $amenities = $property->amenities ? json_decode($property->amenities, true) : [];

            return response()->json([
                'success' => true,
                'message' => 'Property amenities retrieved successfully',
                'data' => [
                    'title' => $property->title,
                    'amenities' => $amenities
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Property amenities retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property amenities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAvailabilityDates($propertyId) {
        // Step 1: Validate the property ID
        if (!is_numeric($propertyId) || $propertyId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid property ID',
            ], 400);
        }

        try {
            // Step 2: Retrieve availability records for the specified property
            $availabilityRecords = Availability::where('property_id', $propertyId)->get();

            // Step 3: Extract available dates
            $availableDates = [];
            foreach ($availabilityRecords as $record) {
                // Assuming 'availability' is an array of dates
                if (isset($record->availability)) {
                    $availableDates = array_merge($availableDates, $record->availability);
                }
            }

            // Step 4: Return the available dates
            return response()->json([
                'success' => true,
                'message' => 'Availability dates retrieved successfully',
                'available_dates' => array_unique($availableDates), // Remove duplicates
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve availability dates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
