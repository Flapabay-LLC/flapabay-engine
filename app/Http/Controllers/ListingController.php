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
        // Extract filters from the request
        $categoryIds = $request->input('category_id', []);
        $propertyTypeIds = $request->input('property_type_id', []);
        $keyword = $request->input('keyword', '');
        $page = $request->input('page', 1); // Default to page 1 if not provided

        // Build query with filters
        $query = Property::query();

        if (!empty($categoryIds)) {
            $query->where(function ($q) use ($categoryIds) {
                foreach ($categoryIds as $categoryId) {
                    $q->orWhereRaw("JSON_CONTAINS(category_id, '\"$categoryId\"')");
                }
            });
        }

        if (!empty($propertyTypeIds)) {
            $query->where(function ($q) use ($propertyTypeIds) {
                foreach ($propertyTypeIds as $propertyTypeId) {
                    $q->orWhereRaw("JSON_CONTAINS(property_type_id, '\"$propertyTypeId\"')");
                }
            });
        }

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->orWhere('title', 'LIKE', "%{$keyword}%")
                ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        // Paginate the results, considering the page number
        $properties = $query->paginate(10, ['*'], 'page', $page);

        // Return paginated results as JSON
        return response()->json([
            'success' => true,
            'data' => $properties->items(),
            'total_results' => $properties->total(),
            'current_page' => $properties->currentPage(),
            'last_page' => $properties->lastPage(),
            'per_page' => $properties->perPage(),
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $listing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListingRequest $request, Listing $listing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        //
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
            $query = Listing::with(['host', 'propertyType', 'amenities', 'images'])
                ->where('status', 'active');

            // Location search
            if ($request->has('location')) {
                $location = $request->location;
                $query->where(function($q) use ($location) {
                    $q->where('city', 'like', "%{$location}%")
                        ->orWhere('state', 'like', "%{$location}%")
                        ->orWhere('country', 'like', "%{$location}%");
                });
            }

            // Price range
            if ($request->has('min_price')) {
                $query->where('price_per_night', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price_per_night', '<=', $request->max_price);
            }

            // Property type
            if ($request->has('property_type_id')) {
                $query->where('property_type_id', $request->property_type_id);
            }

            // Bedrooms
            if ($request->has('bedrooms')) {
                $query->where('bedrooms', '>=', $request->bedrooms);
            }

            // Bathrooms
            if ($request->has('bathrooms')) {
                $query->where('bathrooms', '>=', $request->bathrooms);
            }

            // Guests
            if ($request->has('guests')) {
                $query->where('max_guests', '>=', $request->guests);
            }

            // Amenities
            if ($request->has('amenities')) {
                $amenities = explode(',', $request->amenities);
                $query->whereHas('amenities', function($q) use ($amenities) {
                    $q->whereIn('amenities.id', $amenities);
                });
            }

            // Sort by
            if ($request->has('sort_by')) {
                switch ($request->sort_by) {
                    case 'price_asc':
                        $query->orderBy('price_per_night', 'asc');
                        break;
                    case 'price_desc':
                        $query->orderBy('price_per_night', 'desc');
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

            $listings = $query->paginate(10);

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
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'property_type_id' => 'required|exists:property_types,id',
                'price_per_night' => 'required|numeric|min:0',
                'bedrooms' => 'required|integer|min:1',
                'bathrooms' => 'required|integer|min:1',
                'max_guests' => 'required|integer|min:1',
                'address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'country' => 'required|string',
                'zip_code' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'amenities' => 'array',
                'amenities.*' => 'exists:amenities,id',
                'place_items' => 'array',
                'place_items.*' => 'exists:place_items,id',
                'images' => 'array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
            ]);

            DB::beginTransaction();

            $listing = Listing::create([
                'host_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'property_type_id' => $request->property_type_id,
                'price_per_night' => $request->price_per_night,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'max_guests' => $request->max_guests,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'draft'
            ]);

            if ($request->has('amenities')) {
                $listing->amenities()->attach($request->amenities);
            }

            if ($request->has('place_items')) {
                $listing->placeItems()->attach($request->place_items);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('listings', 'public');
                    $listing->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Listing created successfully',
                'data' => $listing->load(['amenities', 'placeItems', 'images'])
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
                'property_type_id' => 'sometimes|exists:property_types,id',
                'price_per_night' => 'sometimes|numeric|min:0',
                'bedrooms' => 'sometimes|integer|min:1',
                'bathrooms' => 'sometimes|integer|min:1',
                'max_guests' => 'sometimes|integer|min:1',
                'address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'state' => 'sometimes|string',
                'country' => 'sometimes|string',
                'zip_code' => 'sometimes|string',
                'latitude' => 'sometimes|numeric',
                'longitude' => 'sometimes|numeric',
                'status' => 'sometimes|in:draft,active,inactive',
                'amenities' => 'sometimes|array',
                'amenities.*' => 'exists:amenities,id',
                'place_items' => 'sometimes|array',
                'place_items.*' => 'exists:place_items,id',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
            ]);

            DB::beginTransaction();

            $listing->update($request->only([
                'title', 'description', 'property_type_id', 'price_per_night',
                'bedrooms', 'bathrooms', 'max_guests', 'address', 'city',
                'state', 'country', 'zip_code', 'latitude', 'longitude', 'status'
            ]));

            if ($request->has('amenities')) {
                $listing->amenities()->sync($request->amenities);
            }

            if ($request->has('place_items')) {
                $listing->placeItems()->sync($request->place_items);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('listings', 'public');
                    $listing->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Listing updated successfully',
                'data' => $listing->load(['amenities', 'placeItems', 'images'])
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
     * Fetch all listings for a host
     */
    public function fetchHostListings()
    {
        try {
            $listings = Listing::where('host_id', Auth::id())
                ->with(['propertyType', 'amenities', 'images', 'reviews'])
                ->get();

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
     * Delete a host's listing
     */
    public function deleteHostListing($listingId)
    {
        try {
            $listing = Listing::where('host_id', Auth::id())
                ->findOrFail($listingId);

            DB::beginTransaction();

            // Delete related records
            $listing->amenities()->detach();
            $listing->placeItems()->detach();
            $listing->images()->delete();
            $listing->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Listing deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
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
}
