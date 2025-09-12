<?php

namespace App\Http\Controllers;


use App\Http\Requests\StorelistingRequest;
use App\Http\Requests\UpdatelistingRequest;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Stay;
use App\Models\UserReview;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class listingController extends Controller
{
    /**
     * Get a list of listings without filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getlistings(Request $request)
    {
        // dd($request);
        try {
            // Get the page number from the request, default to 1
            $page = $request->input('page', 1);

            // Paginate the listings with 10 items per page, only show published listings
            $listings = Listing::where('status', Listing::STATUS_PUBLISHED)
                ->paginate(10, ['*'], 'page', $page);

            // Return success response with paginated data
            return response()->json([
                'success' => true,
                'data' => $listings->items(),
                'total_results' => $listings->total(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Create a new listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createlistings(StorelistingRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Step 1: Create listing record
            $listing = Listing::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'location' => $validatedData['location'],
                'address' => $validatedData['address'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'check_in_hour' => $validatedData['check_in_hour'],
                'check_out_hour' => $validatedData['check_out_hour'],
                'num_of_guests' => $validatedData['num_of_guests'],
                'num_of_children' => $validatedData['num_of_children'],
                'maximum_guests' => $validatedData['maximum_guests'],
                'country' => $validatedData['country'],
                'currency' => $validatedData['currency'],
                'price_range' => $validatedData['price_range'],
                'price' => $validatedData['price'],
                'additional_guest_price' => $validatedData['additional_guest_price'],
                'children_price' => $validatedData['children_price'],
                'amenities' => json_encode($validatedData['amenities']),
                'house_rules' => json_encode($validatedData['house_rules']),
                'video_link' => $validatedData['video_link'],
                'verified' => $validatedData['verified'],
                'num_of_bedrooms' => $validatedData['num_of_bedrooms'],
                'num_of_bathrooms' => $validatedData['num_of_bathrooms'],
                'num_of_quarters' => $validatedData['num_of_quarters'],
                'user_id' => $validatedData['user_id'],
                'status' => 'draft',
                'listing_type' => 'stay' // Default to stay, can be updated later
            ]);

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
                                'Key'        => 'listings/' . $fileName,
                                'SourceFile' => $image->getPathname(),
                            ]);

                            if (isset($result['ObjectURL'])) {
                                $wasabiUrl = $result['ObjectURL'];
                            } else {
                                throw new Exception('Object URL not returned from Wasabi');
                            }
                        } catch (Exception $e) {
                            // Fallback to local storage
                            $localPath = $image->storeAs('listings', $fileName, 'public');
                            $wasabiUrl = Storage::disk('public')->url($localPath);
                        }

                        $imagePaths[] = $wasabiUrl;
                    }
                }
            }

            // Save image paths to the listing
            if (!empty($imagePaths)) {
                $listing->images = json_encode($imagePaths);
                $listing->save();
            }
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => 'Listing created successfully',
                "listing" => $listing,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "message" => 'Failed to create listing',
                "error" => $e->getMessage(),
            ], 500);
        }
    }


    public function updatelistings(UpdatelistingRequest $request) {
        // Step 1: Validate incoming request data
       

        try {
            DB::beginTransaction();
    
            // Fetch existing listing
            $listing = listing::find($request->input('listing_id'));
            if (!$listing) {
                return response()->json([
                    "success" => false,
                    "message" => 'listing not found.',
                ], 404);
            }
    
            // Update listing data
            $listing->update($request->validated());
    
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
                                'Key'        => 'listings/images/' . $fileName,
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
                            $path = $image->store('listings/images', 'public');
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
                $listing->images = json_encode($imagePaths);
                $listing->save();
            }
    
            DB::commit();
    
            return response()->json([
                "success" => true,
                "message" => 'listing updated successfully',
                "listing" => $listing,
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => 'Failed to update listing',
                "error" => $e->getMessage(),
            ], 500);
        }

    }



    public function deletelisting($listingId) {
        // Step 1: Validate the listing ID
        if (!is_numeric($listingId) || $listingId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid listing ID',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Step 2: Find the listing
            $listing = listing::findOrFail($listingId);

            // Step 3: Delete related Listings
            Listing::where('listing_id', $listingId)->delete();

            // Step 4: Delete related Availability
            Availability::where('listing_id', $listingId)->delete();

            // Step 5: Delete related Bookings
            Booking::where('listing_id', $listingId)->delete();

            // Step 6: Delete the listing
            $listing->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'listing deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function getlisting($listingId) {
        // dd($listingId);
        try {
            // Step 1: Validate the listing ID
            if (!is_numeric($listingId) || $listingId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID',
                ], 400);
            }

            // Step 2: Retrieve the listing with its relationships, only show published listings
            $listing = Listing::with([
                'category' => function($query) {
                    $query->select('id', 'name', 'description');
                },
                'listingType' => function($query) {
                    $query->select('id', 'name', 'description');
                },
                'reviews' => function($query) {
                    $query->select('id', 'listing_id', 'user_id', 'rating', 'review', 'created_at')
                          ->with(['user' => function($q) {
                              $q->select('id', 'fname', 'lname', 'email');
                          }]);
                },
                'stayDetails', // eager load stay details
                'experienceDetails' // eager load experience details
            ])
            ->where('status', Listing::STATUS_PUBLISHED)
            ->find($listingId);
            
            // Only load user relationship if user_id is not null
            if ($listing && $listing->user_id) {
                $listing->load(['user' => function($query) {
                    $query->select('id', 'fname', 'lname', 'email');
                }]);
            }

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found',
                ], 404);
            }

            // Step 3: Format the response data
            $listingData = $listing->toArray();
            
            // Ensure images are properly decoded if they're stored as JSON
            if (isset($listingData['images']) && is_string($listingData['images'])) {
                $listingData['images'] = json_decode($listingData['images'], true) ?? [];
            }

            // Ensure amenities are properly decoded if they're stored as JSON
            if (isset($listingData['amenities']) && is_string($listingData['amenities'])) {
                $listingData['amenities'] = json_decode($listingData['amenities'], true) ?? [];
            }

            // Ensure house_rules are properly decoded if they're stored as JSON
            if (isset($listingData['house_rules']) && is_string($listingData['house_rules'])) {
                $listingData['house_rules'] = json_decode($listingData['house_rules'], true) ?? [];
            }

            // Ensure price_range is properly decoded if it's stored as JSON
            if (isset($listingData['price_range']) && is_string($listingData['price_range'])) {
                $listingData['price_range'] = json_decode($listingData['price_range'], true) ?? [];
            }

            // Calculate average rating from reviews
            if (isset($listingData['reviews']) && !empty($listingData['reviews'])) {
                $listingData['average_rating'] = collect($listingData['reviews'])->avg('rating');
                $listingData['total_reviews'] = count($listingData['reviews']);
            } else {
                $listingData['average_rating'] = 0;
                $listingData['total_reviews'] = 0;
            }

            // Add listing availability (availability fields are now part of listing)
            $listingData['availability'] = [
                'listing_id' => $listing->id,
                'check_in_date' => $listing->check_in_date,
                'check_out_date' => $listing->check_out_date,
                'check_in_hour' => $listing->check_in_hour,
                'check_out_hour' => $listing->check_out_hour,
                'allow_instant_booking' => $listing->allow_instant_booking,
                'cancellation_policy' => $listing->cancellation_policy,
                'availability_type' => $listing->availability_type,
                'flexible_period' => $listing->flexible_period,
                'flexible_month' => $listing->flexible_month,
                'is_available' => !is_null($listing->check_in_date) && !is_null($listing->check_out_date),
            ];

            // Add type-specific data
            if ($listing->listing_type === 'stay' && $listing->stayDetails) {
                $listingData['stay_details'] = $listing->stayDetails->toArray();
            } elseif ($listing->listing_type === 'experience' && $listing->experienceDetails) {
                $listingData['experience_details'] = $listing->experienceDetails->toArray();
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing retrieved successfully',
                'listing' => $listingData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('listing retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getlistingReviews($listingId) {
        // Step 1: Validate the listing ID
        if (!is_numeric($listingId) || $listingId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid listing ID',
            ], 400);
        }

        try {
            // Step 2: Retrieve reviews from UserReview Model where listing_id = $listingId
            $reviews = UserReview::where('listing_id', $listingId)->get();

            // Step 3: Check if reviews exist
            if ($reviews->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No reviews found for this listing',
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


    public function getlistingDescription($listingId) {
        try {
            // Step 1: Validate the listing ID
            if (!is_numeric($listingId) || $listingId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID',
                ], 400);
            }

            // Step 2: Retrieve the listing description
            $listing = Listing::select(['id', 'title', 'description', 'about_place'])
                ->find($listingId);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing description retrieved successfully',
                'data' => [
                    'title' => $listing->title,
                    'description' => $listing->description,
                    'about_place' => $listing->about_place
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Listing description retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listing description',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getlistingPriceDetails($listingId) {
        try {
            // Step 1: Validate the listing ID
            if (!is_numeric($listingId) || $listingId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID',
                ], 400);
            }

            // Step 2: Retrieve the listing price details
            $listing = Listing::select([
                'id',
                'title',
                'currency',
                'price',
                'price_per_night',
                'price_range',
                'additional_guest_price',
                'children_price'
            ])->find($listingId);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found',
                ], 404);
            }

            // Format price range if it exists
            $priceRange = $listing->price_range ? json_decode($listing->price_range, true) : null;

            return response()->json([
                'success' => true,
                'message' => 'Listing price details retrieved successfully',
                'data' => [
                    'title' => $listing->title,
                    'currency' => $listing->currency,
                    'price' => $listing->price,
                    'price_per_night' => $listing->price_per_night,
                    'price_range' => $priceRange,
                    'additional_guest_price' => $listing->additional_guest_price,
                    'children_price' => $listing->children_price
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Listing price details retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listing price details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getlistingAmenities($listingId) {
        try {
            // Step 1: Validate the listing ID
            if (!is_numeric($listingId) || $listingId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID',
                ], 400);
            }

            // Step 2: Retrieve the listing amenities
            $listing = Listing::select(['id', 'amenities'])
                ->find($listingId);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found',
                ], 404);
            }

            // Handle amenities as string or array
            if (is_string($listing->amenities)) {
                $amenities = json_decode($listing->amenities, true) ?? [];
            } elseif (is_array($listing->amenities)) {
                $amenities = $listing->amenities;
            } else {
                $amenities = [];
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing amenities retrieved successfully',
                'data' => [
                    'amenities' => $amenities
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Listing amenities retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listing amenities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get listing availability dates based on check_in_date and check_out_date
     */
    public function getlistingAvailabilityDates($listingId) {
        try {
            // Validate the listing ID
            if (!is_numeric($listingId) || $listingId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID',
                ], 400);
            }

            // Retrieve the listing with relevant fields
            $listing = Listing::select([
                'id', 'check_in_date', 'check_out_date', 'check_in_hour', 
                'check_out_hour', 'allow_instant_booking', 'cancellation_policy', 
                'availability_type', 'flexible_period', 'flexible_month'
            ])->find($listingId);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found',
                ], 404);
            }

            // Return availability information
            return response()->json([
                'success' => true,
                'message' => 'Listing availability dates retrieved successfully',
                'data' => [
                    'listing_id' => $listing->id,
                    'check_in_date' => $listing->check_in_date,
                    'check_out_date' => $listing->check_out_date,
                    'check_in_hour' => $listing->check_in_hour,
                    'check_out_hour' => $listing->check_out_hour,
                    'allow_instant_booking' => $listing->allow_instant_booking,
                    'cancellation_policy' => $listing->cancellation_policy,
                    'availability_type' => $listing->availability_type,
                    'flexible_period' => $listing->flexible_period,
                    'flexible_month' => $listing->flexible_month,
                    'is_available' => !is_null($listing->check_in_date) && !is_null($listing->check_out_date)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listing availability dates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set listing availability dates and related availability fields
     */
    public function setlistingAvailabilityDates(Request $request, $listingId) {
        $validated = $request->validate([
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'check_in_hour' => 'nullable|string',
            'check_out_hour' => 'nullable|string',
            'availability_type' => 'nullable|string|in:range,month,flexible',
            'flexible_period' => 'nullable|string|in:week,weekend,month',
            'flexible_month' => 'nullable|string', // could validate against months if needed
        ]);

        $listing = Listing::find($listingId);
        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found',
            ], 404);
        }

        // Update all availability fields on the listing
        $listing->fill($validated);
        $listing->save();

        return response()->json([
            'success' => true,
            'message' => 'Listing availability updated successfully',
        ], 200);
    }
}
