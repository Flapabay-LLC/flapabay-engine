<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use Illuminate\Http\Request;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Property;
use App\Models\UserReview;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Amenity;
use App\Models\Favorite;
use App\Models\PlaceItem;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemFavourite;
use App\Helpers\CurrencyHelper;
use App\Helpers\GeoLocationHelper;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'keyword' => 'nullable|string',
                'location' => 'nullable|string',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'property_type_id' => 'nullable|integer',
                'bedrooms' => 'nullable|integer|min:0',
                'bathrooms' => 'nullable|integer|min:0',
                'guests' => 'nullable|integer|min:1',
                'amenities' => 'nullable|array',
                'category_id' => 'nullable|integer',
                'sort_by' => 'nullable|in:price_asc,price_desc,rating',
                'per_page' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Listing::with(['property', 'propertyType', 'amenities', 'images', 'reviews'])
                ->whereHas('property', function($q) {
                    $q->where('verified', true);
                });

            // Keyword search
            if ($request->has('keyword')) {
                $keyword = $request->keyword;
                $query->where(function($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                      ->orWhereHas('property', function($q) use ($keyword) {
                          $q->where('description', 'like', "%{$keyword}%")
                            ->orWhere('title', 'like', "%{$keyword}%");
                      });
                });
            }

            // Location search
            if ($request->has('location')) {
                $location = $request->location;
                $query->whereHas('property', function($q) use ($location) {
                    $q->where('location', 'like', "%{$location}%")
                        ->orWhere('address', 'like', "%{$location}%")
                        ->orWhere('country', 'like', "%{$location}%")
                        ->orWhere('neighborhood_area', 'like', "%{$location}%")
                        ->orWhere('city', 'like', "%{$location}%");
                });
            }

            // Price range
            if ($request->has('min_price')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('price_per_night', '>=', $request->min_price);
                });
            }
            if ($request->has('max_price')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('price_per_night', '<=', $request->max_price);
                });
            }

            // Property type
            if ($request->has('property_type_id')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->whereRaw("JSON_CONTAINS(property_type_id, ?)", [json_encode($request->property_type_id)]);
                });
            }

            // Bedrooms
            if ($request->has('bedrooms')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('num_of_bedrooms', '>=', $request->bedrooms);
                });
            }

            // Bathrooms
            if ($request->has('bathrooms')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('num_of_bathrooms', '>=', $request->bathrooms);
                });
            }

            // Guests
            if ($request->has('guests')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('maximum_guests', '>=', $request->guests);
                });
            }

            // Amenities
            if ($request->has('amenities') && !empty($request->amenities)) {
                $amenities = is_array($request->amenities) ? $request->amenities : explode(',', $request->amenities);
                $query->whereHas('amenities', function($q) use ($amenities) {
                    $q->whereIn('amenities.id', $amenities);
                });
            }

            // Category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Sort by
            if ($request->has('sort_by')) {
                switch ($request->sort_by) {
                    case 'price_asc':
                        $query->whereHas('property', function($q) {
                            $q->orderBy('price_per_night', 'asc');
                        });
                        break;
                    case 'price_desc':
                        $query->whereHas('property', function($q) {
                            $q->orderBy('price_per_night', 'desc');
                        });
                        break;
                    case 'rating':
                        $query->withAvg('reviews', 'rating')
                            ->orderBy('reviews_avg_rating', 'desc');
                        break;
                    default:
                        $query->latest();
                }
            } else {
                $query->latest();
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $listings = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Listings fetched successfully',
                'data' => $listings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return response()->json([
        'success' => true,
        'message' => 'Form data fetched for creating a listing',
        'data' => [
            'property_types' => \App\Models\PropertyType::all(),
            'amenities' => \App\Models\Amenity::all(),
            'place_items' => \App\Models\PlaceItem::all(),
        ]
    ]);
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListingRequest $request)
    {

        // Step 1: Validate incoming request data
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'check_in_hour' => 'required|string|max:10',
            'check_out_hour' => 'required|string|max:10',
            'num_of_guests' => 'required|integer',
            'num_of_children' => 'nullable|integer',
            'maximum_guests' => 'required|integer',
            // 'allow_extra_guests' => 'boolean',
            // 'neighborhood_area' => 'nullable|string|max:255',
            // 'county' => 'nullable',
            'country' => 'required|string|max:255',
            // 'show_contact_form_instead_of_booking' => 'boolean',
            // 'allow_instant_booking' => 'boolean',
            'currency' => 'required|string|max:10',
            'price_range' => 'required|string|max:50',
            'price' => 'required|numeric',
            // 'price_per_night' => 'required|numeric',
            'additional_guest_price' => 'nullable|numeric',
            'children_price' => 'nullable|numeric',
            'amenities' => 'nullable|array',
            'house_rules' => 'nullable|array',
            'page' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric',
            'favorite' => 'boolean',
            'images' => 'nullable|array',
            'video_link' => 'nullable',
            'verified' => 'boolean',
            'property_type_id' => 'nullable',
            'category_id' => 'required',
            'tags' => 'nullable',
            'listing_type' => 'required|string',
            'num_of_bedrooms' => 'required|integer',
            'num_of_bathrooms' => 'required|integer',
            'num_of_quarters' => 'nullable|integer',
            // Add any other fields you need to validate
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validatedData->errors(),
            ], 422);
        }
        try {
            DB::beginTransaction();

            // Step 2: Insert into Property Model
            $property = Property::createProperty($validatedData->validated());

            // Step 3: Handle image uploads to Wasabi
            $imagePaths = [];
            if ($request->has('images')) {
                // Define your Wasabi S3 endpoint and credentials
                $endpoint = 'https://s3.us-west-1.wasabisys.com';  // Replace with your Wasabi region endpoint
                $bucketName = 'flapapic';                     // Replace with your Wasabi bucket name
                $region = 'us-west-1';                             // Replace with your Wasabi region
                $accessKey = 'HJG2GQM9QGBE4K6JCO2S';                      // Replace with your Wasabi access key
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

                foreach ($request->file('images') as $image) {
                    // Validate the image file
                    if ($image->isValid()) {
                        // Generate a unique file name
                        $fileName = time() . '_' . $image->getClientOriginalName();

                        // Attempt to upload the image to the Wasabi bucket
                        $result = $s3Client->putObject([
                            'Bucket'     => $bucketName,
                            'Key'        => 'properties/' . $fileName,
                            'SourceFile' => $image->getPathname(),  // Use the temporary file path
                        ]);

                        // Check if the file was successfully uploaded
                        if (isset($result['ObjectURL'])) {
                            $imagePaths[] = $result['ObjectURL']; // Store the URL of the uploaded image
                        } else {
                            throw new Exception('Failed to upload image to Wasabi.');
                        }
                    }
                }
            }

            // Save image paths to the property (if applicable)
            if (!empty($imagePaths)) {
                $property->images = json_encode($imagePaths); // Store as JSON or adjust as needed
                $property->save();
            }

            // // Step 4: Insert into Listing Model
            $listingData = [
                'title' => $request->input('title'), // You can customize this as needed
                'property_id' => $property->id,
                'post_levels' => $request->input('post_levels', null), // Assuming this is optional
                'published_at' => Carbon::now(), // Set to current time or customize as needed
                'status' => 0, // Set default status or customize
                'listing_type' => $request->input('listing_type'), // Listing type
                'host_id' => $request->input('host_id'), // Listing type
            ];

            $listing = Listing::create($listingData);

            DB::commit();
            return response()->json([
                "success" => true,
                "message" => 'Property created successfully',
                "property" => $property,
                "listing" => $listing,
            ], 201);

        } catch (\Exception $e) {
            dd($e);
            // DB::rollBack();
            // return response()->json([
            //     "success" => false,
            //     "message" => 'Failed to create property',
            //     "error" => $e->getMessage(),
            // ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Listing fetched successfully',
            'data' => $listing->load(['property', 'propertyType', 'amenities', 'images', 'reviews'])
        ], 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $listing)
    {
         // Typically for web UI. You can return JSON for API:
        return response()->json([
            'success' => true,
            'data' => $listing
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListingRequest $request, Listing $listing)
    {
        $listing->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Listing updated successfully',
            'data' => $listing
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        //
        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully'
        ]);
    }

    /**
     * Get all system amenities
     */
    public function getSystemAmenities()
    {
        try {
            $amenities = Amenity::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Amenities fetched successfully',
                'data' => $amenities
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch amenities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all system favorites
     */
    public function getSystemFavorites()
    {
        try {
            $favorites = SystemFavourite::all();
            
            return response()->json([
                'status' => 'success',
                'message' => 'System favorites retrieved successfully',
                'data' => $favorites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve system favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all system place items
     */
    public function getSystemPlaceItems()
    {
        try {
            $placeItems = PlaceItem::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Place items fetched successfully',
                'data' => $placeItems
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch place items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all system property types
     */
    public function getSystemPropertyTypes()
    {
        try {
            $propertyTypes = PropertyType::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Property types fetched successfully',
                'data' => $propertyTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch property types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search listings with filters
     */
    public function searchListings(Request $request)
    {
        try {
            $query = Listing::with(['property', 'propertyType', 'amenities', 'images', 'reviews'])
                ->whereHas('property', function($q) {
                    $q->where('verified', true);
                });

            // Location search
            if ($request->has('location')) {
                $location = $request->location;
                $query->whereHas('property', function($q) use ($location) {
                    $q->where('location', 'like', "%{$location}%")
                        ->orWhere('address', 'like', "%{$location}%")
                        ->orWhere('country', 'like', "%{$location}%")
                        ->orWhere('neighborhood_area', 'like', "%{$location}%")
                        ->orWhere('city', 'like', "%{$location}%");
                });
            }

            // Price range
            if ($request->has('min_price')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('price_per_night', '>=', $request->min_price);
                });
            }
            if ($request->has('max_price')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('price_per_night', '<=', $request->max_price);
                });
            }

            // Property type
            if ($request->has('property_type_id')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->whereRaw("JSON_CONTAINS(property_type_id, ?)", [json_encode($request->property_type_id)]);
                });
            }

            // Bedrooms
            if ($request->has('bedrooms')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('num_of_bedrooms', '>=', $request->bedrooms);
                });
            }

            // Bathrooms
            if ($request->has('bathrooms')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('num_of_bathrooms', '>=', $request->bathrooms);
                });
            }

            // Guests
            if ($request->has('guests')) {
                $query->whereHas('property', function($q) use ($request) {
                    $q->where('maximum_guests', '>=', $request->guests);
                });
            }

            // Amenities
            if ($request->has('amenities') && !empty($request->amenities)) {
                $amenities = is_array($request->amenities) ? $request->amenities : explode(',', $request->amenities);
                $query->whereHas('amenities', function($q) use ($amenities) {
                    $q->whereIn('amenities.id', $amenities);
                });
            }

            // Category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Sort by
            if ($request->has('sort_by')) {
                switch ($request->sort_by) {
                    case 'price_asc':
                        $query->whereHas('property', function($q) {
                            $q->orderBy('price_per_night', 'asc');
                        });
                        break;
                    case 'price_desc':
                        $query->whereHas('property', function($q) {
                            $q->orderBy('price_per_night', 'desc');
                        });
                        break;
                    case 'rating':
                        $query->withAvg('reviews', 'rating')
                            ->orderBy('reviews_avg_rating', 'desc');
                        break;
                    default:
                        $query->latest();
                }
            } else {
                $query->latest();
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $listings = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Listings fetched successfully',
                'data' => $listings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new listing
     */
    public function createNewListing(Request $request)
    {
        try {
            $request->validate([
                'host_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'address' => 'required|string',
                'location' => 'required|string',
                'price' => 'required|numeric|min:0',
                'price_per_night' => 'required|numeric|min:0',
                'weekend_price' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percentage,fixed',
                'discount_value' => 'nullable|numeric|min:0',
                'currency' => 'required|string|size:3',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'city' => 'required|string',
                'country' => 'required|string',
                'check_in_hour' => 'required|string',
                'check_out_hour' => 'required|string',
                'num_of_guests' => 'required|integer|min:1',
                'num_of_children' => 'nullable|integer|min:0',
                'maximum_guests' => 'required|integer|min:1',
                'allow_extra_guests' => 'boolean',
                'neighborhood_area' => 'nullable|string',
                'show_contact_form_instead_of_booking' => 'boolean',
                'allow_instant_booking' => 'boolean',
                'additional_guest_price' => 'nullable|numeric|min:0',
                'children_price' => 'nullable|numeric|min:0',
                'amenities' => 'nullable|array',
                'house_rules' => 'nullable|array',
                'video_link' => 'nullable|string',
                'property_type_id' => 'required|exists:property_types,id',
                'category_id' => 'required|exists:categories,id',
                'place_items' => 'nullable|array',
                'first_reserver' => 'required|string',
                'host_type' => 'required|in:Private Individual,Business',
                'num_of_bedrooms' => 'required|integer|min:1',
                'num_of_bathrooms' => 'required|integer|min:1',
                'num_of_quarters' => 'nullable|integer|min:0',
                'has_unallocated_rooms' => 'boolean',
                'listing_type' => 'required|string',
                'images' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            // Create the property
            $property = Property::create([
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'check_in_hour' => $request->check_in_hour,
                'check_out_hour' => $request->check_out_hour,
                'num_of_guests' => $request->num_of_guests,
                'num_of_children' => $request->num_of_children,
                'maximum_guests' => $request->maximum_guests,
                'allow_extra_guests' => $request->allow_extra_guests === 'true',
                'neighborhood_area' => $request->neighborhood_area,
                'country' => $request->country,
                'show_contact_form_instead_of_booking' => $request->show_contact_form_instead_of_booking === 'true',
                'allow_instant_booking' => $request->allow_instant_booking === 'true',
                'currency' => $request->currency,
                'price' => $request->price,
                'price_per_night' => $request->price_per_night,
                'additional_guest_price' => $request->additional_guest_price,
                'children_price' => $request->children_price,
                'amenities' => json_encode($request->amenities),
                'house_rules' => json_encode($request->house_rules),
                'video_link' => json_encode($request->video_link),
                'property_type_id' => json_encode($request->property_type_id),
                'category_id' => json_encode($request->category_id),
                'place_items' => json_encode($request->place_items),
                'verified' => $request->verified === '1',
                'about_place' => $request->about_place,
                'host_type' => $request->host_type,
                'num_of_bedrooms' => $request->num_of_bedrooms,
                'num_of_bathrooms' => $request->num_of_bathrooms,
                'num_of_quarters' => $request->num_of_quarters,
                'has_unallocated_rooms' => $request->has_unallocated_rooms === '1',
                'first_reserver' => $request->first_reserver
            ]);

            // Handle image uploads
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
                        
                        $result = $s3Client->putObject([
                            'Bucket'     => $bucketName,
                            'Key'        => 'properties/' . $fileName,
                            'SourceFile' => $image->getPathname(),
                        ]);

                        if (isset($result['ObjectURL'])) {
                            $imagePaths[] = $result['ObjectURL'];
                        }
                    }
                }
            }

            // Create the listing
            $listing = Listing::create([
                'host_id' => $request->host_id,
                'title' => $request->title,
                'property_id' => $property->id,
                'category_id' => $request->category_id[0],
                'status' => false,
                'published_at' => now(),
                'cancellation_policy' => false,
                'is_completed' => false,
                'listing_type' => $request->listing_type
            ]);

            // Save images to the listing_images table
            if (!empty($imagePaths)) {
                foreach ($imagePaths as $imagePath) {
                    $listing->images()->create([
                        'image_url' => $imagePath,
                        'is_primary' => false
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Listing created successfully',
                'data' => [
                    'property' => $property,
                    'listing' => $listing->load('images')
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a host's listing
     */
    public function updateHostListing(Request $request, $listingId)
    {

        try {
            $listing = Listing::where('host_id', Auth::id())
                ->findOrFail($listingId);

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'address' => 'sometimes|string',
                'location' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'price_per_night' => 'sometimes|numeric|min:0',
                'weekend_price' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percentage,fixed',
                'discount_value' => 'nullable|numeric|min:0',
                'currency' => 'sometimes|string|size:3',
                'latitude' => 'sometimes|numeric',
                'longitude' => 'sometimes|numeric',
                'city' => 'sometimes|string',
                'country' => 'sometimes|string',
                'check_in_hour' => 'sometimes|string',
                'check_out_hour' => 'sometimes|string',
                'num_of_guests' => 'sometimes|integer|min:1',
                'num_of_children' => 'nullable|integer|min:0',
                'maximum_guests' => 'sometimes|integer|min:1',
                'allow_extra_guests' => 'boolean',
                'neighborhood_area' => 'nullable|string',
                'show_contact_form_instead_of_booking' => 'boolean',
                'allow_instant_booking' => 'boolean',
                'additional_guest_price' => 'nullable|numeric|min:0',
                'children_price' => 'nullable|numeric|min:0',
                'amenities' => 'nullable|array',
                'house_rules' => 'nullable|array',
                'video_link' => 'nullable|string',
                'property_type_id' => 'sometimes|exists:property_types,id',
                'category_id' => 'sometimes|exists:categories,id',
                'place_items' => 'nullable|array',
                'first_reserver' => 'sometimes|string',
                'host_type' => 'sometimes|in:Private Individual,Business',
                'num_of_bedrooms' => 'sometimes|integer|min:1',
                'num_of_bathrooms' => 'sometimes|integer|min:1',
                'num_of_quarters' => 'nullable|integer|min:0',
                'has_unallocated_rooms' => 'boolean',
                'status' => 'sometimes|boolean',
                'cancellation_policy' => 'sometimes|boolean'
            ]);

            DB::beginTransaction();

            // Update the property
            $property = Property::findOrFail($listing->property_id);
            $property->update([
                'title' => $request->input('title', $property->title),
                'description' => $request->input('description', $property->description),
                'location' => $request->input('location', $property->location),
                'address' => $request->input('address', $property->address),
                'latitude' => $request->input('latitude', $property->latitude),
                'longitude' => $request->input('longitude', $property->longitude),
                'check_in_hour' => $request->input('check_in_hour', $property->check_in_hour),
                'check_out_hour' => $request->input('check_out_hour', $property->check_out_hour),
                'num_of_guests' => $request->input('num_of_guests', $property->num_of_guests),
                'num_of_children' => $request->input('num_of_children', $property->num_of_children),
                'maximum_guests' => $request->input('maximum_guests', $property->maximum_guests),
                'allow_extra_guests' => $request->has('allow_extra_guests') ? $request->allow_extra_guests === 'true' : $property->allow_extra_guests,
                'neighborhood_area' => $request->input('neighborhood_area', $property->neighborhood_area),
                'country' => $request->input('country', $property->country),
                'show_contact_form_instead_of_booking' => $request->has('show_contact_form_instead_of_booking') ? $request->show_contact_form_instead_of_booking === 'true' : $property->show_contact_form_instead_of_booking,
                'allow_instant_booking' => $request->has('allow_instant_booking') ? $request->allow_instant_booking === 'true' : $property->allow_instant_booking,
                'currency' => $request->input('currency', $property->currency),
                'price' => $request->input('price', $property->price),
                'price_per_night' => $request->input('price_per_night', $property->price_per_night),
                'additional_guest_price' => $request->input('additional_guest_price', $property->additional_guest_price),
                'children_price' => $request->input('children_price', $property->children_price),
                'amenities' => $request->has('amenities') ? json_encode($request->amenities) : $property->amenities,
                'house_rules' => $request->has('house_rules') ? json_encode($request->house_rules) : $property->house_rules,
                'video_link' => $request->has('video_link') ? json_encode($request->video_link) : $property->video_link,
                'property_type_id' => $request->has('property_type_id') ? json_encode($request->property_type_id) : $property->property_type_id,
                'category_id' => $request->has('category_id') ? json_encode($request->category_id) : $property->category_id,
                'place_items' => $request->has('place_items') ? json_encode($request->place_items) : $property->place_items,
                'verified' => $request->has('verified') ? $request->verified === '1' : $property->verified,
                'about_place' => $request->input('about_place', $property->about_place),
                'host_type' => $request->input('host_type', $property->host_type),
                'num_of_bedrooms' => $request->input('num_of_bedrooms', $property->num_of_bedrooms),
                'num_of_bathrooms' => $request->input('num_of_bathrooms', $property->num_of_bathrooms),
                'num_of_quarters' => $request->input('num_of_quarters', $property->num_of_quarters),
                'has_unallocated_rooms' => $request->has('has_unallocated_rooms') ? $request->has_unallocated_rooms === '1' : $property->has_unallocated_rooms,
                'first_reserver' => $request->input('first_reserver', $property->first_reserver)
            ]);

            // Update the listing
            $listing->update([
                'title' => $request->input('title', $listing->title),
                'category_id' => $request->has('category_id') ? $request->category_id[0] : $listing->category_id,
                'status' => $request->has('status') ? $request->status : $listing->status,
                'cancellation_policy' => $request->has('cancellation_policy') ? $request->cancellation_policy : $listing->cancellation_policy
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Listing updated successfully',
                'data' => [
                    'property' => $property,
                    'listing' => $listing
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all listings for a host with pagination and filters
     */
    public function fetchHostListings(Request $request)
    {
        try {
            $query = Listing::where('host_id', Auth::id())
                ->with([
                    'propertyType',
                    'amenities',
                    'images',
                    'reviews',
                    'property' // Add property relationship
                ]);

            // Add status filter if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Add search filter if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('property', function($q) use ($search) {
                          $q->where('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%");
                      });
                });
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->input('per_page', 10);
            $listings = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Host listings fetched successfully',
                'data' => $listings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch host listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a host's listing and all related data
     */
    public function deleteHostListing($listingId)
    {
        try {
            $listing = Listing::where('host_id', Auth::id())
                ->with(['property', 'bookings']) // Eager load relationships
                ->findOrFail($listingId);

            // Check if there are any active bookings
            $activeBookings = $listing->bookings()
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->exists();

            if ($activeBookings) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete listing with active bookings'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Delete related records
                $listing->amenities()->detach();
                $listing->placeItems()->detach();
                $listing->images()->delete();
                
                // Delete associated property
                if ($listing->property) {
                    $listing->property->delete();
                }

                // Delete the listing
                $listing->delete();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Listing and all related data deleted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new system amenity
     */
    public function createSystemAmenity(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:amenities',
                'icon' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            $amenity = Amenity::create([
                'name' => $request->name,
                'icon' => $request->icon,
                'description' => $request->description
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Amenity created successfully',
                'data' => $amenity
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create amenity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new system favorite
     */
    public function createSystemFavorite(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:system_favourites',
                'icon' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            $favorite = SystemFavourite::create([
                'name' => $request->name,
                'icon' => $request->icon,
                'description' => $request->description
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'System favorite created successfully',
                'data' => $favorite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create system favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new property type
     */
    public function createSystemPropertyType(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:property_types',
                'icon' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            $propertyType = PropertyType::create([
                'name' => $request->name,
                'icon' => $request->icon,
                'description' => $request->description
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Property type created successfully',
                'data' => $propertyType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create property type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all listings with their relationships and favorite status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllListings()
    {
        try {
            // Fetch all listings with their relationships
            $listings = Listing::with([
                'property',
                'images',
                'amenities',
                'propertyType',
                'host',
                'reviews'
            ])->get();

            // Get user's favorite listings if user is authenticated
            $userFavorites = [];
            $userCurrency = 'USD'; // Default currency

            if (auth()->check()) {
                $userFavorites = Favorite::where('user_id', auth()->id())
                    ->pluck('property_id')
                    ->toArray();
                
                // Get user's preferred currency
                $userCurrency = auth()->user()->currency ?? 'USD';
            } else {
                // For non-authenticated users, determine currency based on IP
                $userCurrency = \App\Helpers\GeoLocationHelper::getCurrencyFromIP();
            }

            // Transform the response
            $listings = $listings->map(function ($listing) use ($userFavorites, $userCurrency) {
                $property = $listing->property;
                
                // Convert prices if property exists and has a different currency
                $price = $property ? $property->price : null;
                $pricePerNight = $property ? $property->price_per_night : null;
                $additionalGuestPrice = $property ? $property->additional_guest_price : null;
                $childrenPrice = $property ? $property->children_price : null;
                $propertyCurrency = $property ? $property->currency : 'USD';

                // Store original prices before conversion
                $originalPrices = null;
                if ($property && $propertyCurrency !== $userCurrency) {
                    $originalPrices = [
                        'price' => $price,
                        'price_per_night' => $pricePerNight,
                        'additional_guest_price' => $additionalGuestPrice,
                        'children_price' => $childrenPrice,
                        'currency' => $propertyCurrency
                    ];

                    $price = \App\Helpers\CurrencyHelper::convert($price, $propertyCurrency, $userCurrency);
                    $pricePerNight = \App\Helpers\CurrencyHelper::convert($pricePerNight, $propertyCurrency, $userCurrency);
                    $additionalGuestPrice = \App\Helpers\CurrencyHelper::convert($additionalGuestPrice, $propertyCurrency, $userCurrency);
                    $childrenPrice = \App\Helpers\CurrencyHelper::convert($childrenPrice, $propertyCurrency, $userCurrency);
                }

                // Get primary image and other images
                $images = $listing->images->map(function($image) {
                    return [
                        'url' => $image->image_url,
                        'is_primary' => $image->is_primary
                    ];
                });

                return [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'description' => $property ? $property->description : null,
                    'location' => $property ? $property->location : null,
                    'price' => $price,
                    'price_per_night' => $pricePerNight,
                    'additional_guest_price' => $additionalGuestPrice,
                    'children_price' => $childrenPrice,
                    'currency' => $userCurrency,
                    'original_prices' => $originalPrices,
                    'maximum_guests' => $property ? $property->maximum_guests : null,
                    'rating' => $property ? $property->rating : null,
                    'verified' => $property ? $property->verified : false,
                    'is_favorite' => in_array($listing->property_id, $userFavorites),
                    'images' => $images,
                    'amenities' => $listing->amenities ? $listing->amenities->pluck('name') : [],
                    'property_type' => $property->propertyType ? $property->propertyType : null,
                    'listing_type' => $listing->listing_type ? $listing->listing_type : null,
                    'host' => $listing->host ? [
                        'id' => $listing->host->id,
                        'name' => $listing->host->fname . ' ' . $listing->host->lname,
                        'email' => $listing->host->email,
                        'phone' => $listing->host->phone
                    ] : null,
                    'reviews' => $listing->reviews ? $listing->reviews->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'rating' => $review->rating,
                            'comment' => $review->comment,
                            'created_at' => $review->created_at
                        ];
                    }) : [],
                    'created_at' => $listing->created_at,
                    'updated_at' => $listing->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Listings fetched successfully',
                'data' => $listings
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
