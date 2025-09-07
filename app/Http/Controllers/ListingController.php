<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Stay;
use App\Models\Experience;
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
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        // dd($request);
        try {
            $query = \App\Models\Property::with([
                'listing',
                'propertyType',
                'images',
                'reviews'
            ]);

            $query->where(function($q) use ($request) {
                // Keyword
                if ($request->filled('keyword')) {
                    $keyword = $request->keyword;
                    $q->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                }

                // Location
                if ($request->filled('location')) {
                    $location = $request->location;
                    $q->orWhere('location', 'like', "%{$location}%")
                      ->orWhere('address', 'like', "%{$location}%")
                      ->orWhere('country', 'like', "%{$location}%")
                      ->orWhere('neighborhood_area', 'like', "%{$location}%")
                      ->orWhere('city', 'like', "%{$location}%");
                }

                // Price range
                if ($request->filled('min_price')) {
                    $q->orWhere('price_per_night', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->orWhere('price_per_night', '<=', $request->max_price);
                }

                // Property type
                if ($request->filled('property_type_id')) {
                    $q->orWhere('property_type_id', $request->property_type_id);
                }

                // Bedrooms
                if ($request->filled('bedrooms')) {
                    $q->orWhere('num_of_bedrooms', '>=', $request->bedrooms);
                }

                // Bathrooms
                if ($request->filled('bathrooms')) {
                    $q->orWhere('num_of_bathrooms', '>=', $request->bathrooms);
                }

                // Guests
                if ($request->filled('children_guests')) {
                    $q->orWhere('children_guests', '>=', $request->children_guests);
                }
                if ($request->filled('infant_guests')) {
                    $q->orWhere('infant_guests', '>=', $request->infant_guests);
                }
                if ($request->filled('adult_guests')) {
                    $q->orWhere('adult_guests', '>=', $request->adult_guests);
                }
                if ($request->filled('pet_guests')) {
                    $q->orWhere('pet_guests', '>=', $request->pet_guests);
                }

                // Square feet
                if ($request->filled('min_square_feet')) {
                    $q->orWhere('square_feet', '>=', $request->min_square_feet);
                }
                if ($request->filled('max_square_feet')) {
                    $q->orWhere('square_feet', '<=', $request->max_square_feet);
                }

                // Property ID
                if ($request->filled('property_id')) {
                    $q->orWhere('id', $request->property_id);
                }

                // Listing type (via relationship)
                if ($request->filled('listing_type')) {
                    $q->orWhereHas('listing', function($subQ) use ($request) {
                        $subQ->where('listing_type', $request->listing_type);
                    });
                }

                // JSON fields: amenities, house_rules, favorites, place_items (search by name, not ID)
                foreach (['amenities', 'house_rules', 'favorites', 'place_items'] as $jsonField) {
                    if ($request->filled($jsonField)) {
                        $values = $request->input($jsonField);
                        if (is_string($values) && $this->isJson($values)) {
                            $values = json_decode($values, true);
                        }
                        foreach ((array)$values as $val) {
                            $q->orWhereJsonContains($jsonField, $val);
                        }
                    }
                }

                // Dates filter with date_search_type logic
                if ($request->filled('dates')) {
                    $dateType = $request->input('date_search_type', 'range');
                    $dates = $request->input('dates');
                    if (is_array($dates) && count($dates) === 1 && is_string($dates[0]) && $this->isJson($dates[0])) {
                        $dates = json_decode($dates[0], true);
                    }
                    
                    if ($dateType === 'range' && is_array($dates) && count($dates) === 2) {
                        $start = $dates[0];
                        $end = $dates[1];
                        $q->orWhere(function($subQ) use ($start, $end) {
                            $subQ->whereNull('check_in_date')
                                 ->orWhere(function($subSubQ) use ($start, $end) {
                                     $subSubQ->where('check_in_date', '<=', $start)
                                            ->where('check_out_date', '>=', $end);
                            });
                        });
                    } elseif ($dateType === 'month' && is_numeric($dates)) {
                        $monthsFromNow = (int)$dates;
                        $targetMonth = now()->addMonths($monthsFromNow)->format('m');
                        $targetYear = now()->addMonths($monthsFromNow)->format('Y');
                        $q->orWhere(function($subQ) use ($targetMonth, $targetYear) {
                            $subQ->whereNull('check_in_date')
                                 ->orWhere(function($subSubQ) use ($targetMonth, $targetYear) {
                                     $subSubQ->whereMonth('check_in_date', $targetMonth)
                                            ->whereYear('check_in_date', $targetYear);
                                 });
                        });
                    } elseif ($dateType === 'flexible' && is_string($dates)) {
                        $flexValue = $request->input('which_flexible_value'); // this will be either week, weekend, or month
                        $flexMonth = $request->input('which_flexible_month'); // this will be either January, February ...etc
                        
                        $q->orWhere(function($subQ) use ($flexValue, $flexMonth) {
                            $subSubQ = $subQ->whereNull('check_in_date');
                            
                            if ($flexMonth) {
                                $monthNumber = $this->getMonthNumber($flexMonth);
                                if ($monthNumber) {
                                    $subSubQ->orWhere(function($subSubSubQ) use ($monthNumber, $flexValue) {
                                        $subSubSubQ->whereMonth('check_in_date', $monthNumber);
                                        
                                        if ($flexValue === 'week') {
                                            $subSubSubQ->whereRaw('DAY(check_in_date) BETWEEN 1 AND 7');
                                        } elseif ($flexValue === 'weekend') {
                                            $subSubSubQ->whereRaw('DAYOFWEEK(check_in_date) IN (1, 7)'); // Sunday = 1, Saturday = 7
                                        } elseif ($flexValue === 'month') {
                                            // Already filtered by month
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        // Default date range search
                        $q->orWhere(function($subQ) use ($dates) {
                            $subSubQ = $subQ->whereNull('check_in_date');
                            foreach ((array)$dates as $date) {
                                $subSubQ->orWhere(function($subSubSubQ) use ($date) {
                                    $subSubSubQ->where('check_in_date', '<=', $date)
                                              ->where('check_out_date', '>=', $date);
                                });
                            }
                        });
                    }
                }

                // Direct check_in_date and check_out_date filtering
                if ($request->has('check_in_date') && $request->has('check_out_date')) {
                    $q->orWhere(function($q2) use ($request) {
                        $q2->whereNull('check_in_date')
                           ->orWhere(function($q3) use ($request) {
                               $q3->where('check_in_date', '<=', $request->check_in_date)
                                  ->where('check_out_date', '>=', $request->check_out_date);
                           });
                    });
                }
            });

            // Sorting, pagination, and response
            $perPage = $request->input('per_page', 10);
            $properties = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Properties fetched successfully',
                'data' => $properties
            ], 200);
        } catch (\Exception $e) {

            // dd($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper to check if a string is JSON (if not already present)
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // Helper to convert month name to number
    private function getMonthNumber($monthName)
    {
        $months = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12
        ];
        
        return $months[strtolower($monthName)] ?? null;
    }

    /**
     * Create a new listing
     */
    public function createNewListing(Request $request)
    {
        // dd($request);
        try {
            // Get user_id from authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json(['errors' => ['auth' => ['Authentication required.']]], 401);
            }
            $userId = $user->id;
            if (!$userId) {
                return response()->json(['errors' => ['user_id' => ['User not authenticated.']]], 422);
            }

            // 1. Find or create a draft property for this user
            $draftId = $request->input('draft_id');
            $property = null;
            if ($draftId) {
                $property = Property::where('id', $draftId)
                    ->where('user_id', $userId)
                    ->where('status', Property::STATUS_DRAFT)
                    ->first();
            }
            if (!$property) {
                // If no draft_id, try to find an existing draft for this user
                $property = Property::where('user_id', $userId)
                    ->where('status', Property::STATUS_DRAFT)
                    ->first();
            }
            if (!$property) {
                // Only create a new draft if none exists for this user
                $property = new Property(['status' => Property::STATUS_DRAFT, 'user_id' => $userId, 'is_host' => true]);
                $property->save();
            }

            // Handle array fields: amenities, house_rules, favorites, place_items
            foreach (['amenities', 'house_rules', 'favorites', 'place_items'] as $jsonField) {
                if ($request->has($jsonField)) {
                    $value = $request->input($jsonField);
                    // If it's a string and is valid JSON, decode it
                    if (is_string($value) && $this->isJson($value)) {
                        $value = json_decode($value, true);
                    }
                    // If it's an array with a single JSON string, decode it
                    if (is_array($value) && count($value) === 1 && is_string($value[0]) && $this->isJson($value[0])) {
                        $value = json_decode($value[0], true);
                    }
                    // Always encode as JSON for storage in the property field
                    $property->$jsonField = json_encode($value);
                    $property->save();
                }
            }

            // 2. Only validate fields present in the request
            $rules = [
                'title' => 'string|max:255',
                'description' => 'string',
                'address' => 'string',
                'location' => 'string',
                'price' => 'numeric|min:0',
                'price_per_night' => 'numeric|min:0',
                'weekend_price' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percentage,fixed',
                'discount_value' => 'nullable|numeric|min:0',
                'currency' => 'string|size:3',
                'latitude' => 'numeric',
                'longitude' => 'numeric',
                'city' => 'string',
                'country' => 'string',
                'check_in_hour' => 'string',
                'check_out_hour' => 'string',
                'num_of_guests' => 'integer|min:1',
                'num_of_children' => 'nullable|integer|min:0',
                'maximum_guests' => 'integer|min:1',
                'allow_extra_guests' => 'boolean',
                'neighborhood_area' => 'nullable|string',
                'show_contact_form_instead_of_booking' => 'boolean',
                'allow_instant_booking' => 'boolean',
                'additional_guest_price' => 'nullable|numeric|min:0',
                'children_price' => 'nullable|numeric|min:0',
                'amenities' => 'nullable|array',
                'house_rules' => 'nullable|array',
                'favorites' => 'nullable|array',
                'video_link' => 'nullable|string',
                'property_type_id' => 'exists:property_types,id',
                'category_id' => 'exists:categories,id',
                'place_items' => 'nullable|array',
                'first_reserver' => 'string',
                'host_type' => 'in:Private Individual,Business',
                'num_of_bedrooms' => 'integer|min:1',
                'num_of_bathrooms' => 'integer|min:1',
                'num_of_quarters' => 'nullable|integer|min:0',
                'has_unallocated_rooms' => 'boolean',
                'listing_type' => 'string',
                'nights' => 'nullable|integer|min:1',
                'check_in_date' => 'nullable|date',
                'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
                'images' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'verified' => 'boolean',
                'about_place' => 'nullable|string',
            ];
            $fieldsToValidate = array_intersect_key($rules, $request->all());
            $validator = Validator::make($request->all(), array_intersect_key($rules, $fieldsToValidate));
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // 3. Update the draft with the new fields and user_id if not set
            $property->fill($request->only(array_keys($fieldsToValidate)));
            if (!$property->user_id) {
                $property->user_id = $userId;
                $property->is_host = true;
            }
            $property->status = Property::STATUS_DRAFT;
            $property->save();

            // 3.5. Create or update listing record
            $listing = Listing::firstOrCreate(
                ['property_id' => $property->id],
                [
                    'host_id' => $user->host_id,
                    'listing_type' => $request->input('listing_type', 'stay'),
                    'status' => Listing::STATUS_DRAFT,
                    'is_completed' => false
                ]
            );

            // Update listing with any provided fields
            if ($request->has('listing_type')) {
                $listing->listing_type = $request->input('listing_type');
                $listing->save();
            }

            // Handle type-specific data
            $this->handleTypeSpecificData($request, $listing);

            // 4. Handle image uploads (Wasabi/local) and save URLs to images JSON column
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

            if (!empty($imagePaths)) {
                $property->images = json_encode($imagePaths);
                $property->save();
            }

            // 5. If this is the final step, validate all required fields and mark as complete
            if ($request->input('finalize')) {
                $property->fill($request->all());
                foreach (['amenities', 'house_rules', 'favorites', 'place_items'] as $jsonField) {
                    if ($request->has($jsonField)) {
                        $value = $request->input($jsonField);
                        if (is_string($value) && $this->isJson($value)) {
                            $value = json_decode($value, true);
                        }
                        if (is_array($value) && count($value) === 1 && is_string($value[0]) && $this->isJson($value[0])) {
                            $value = json_decode($value[0], true);
                        }
                        $property->$jsonField = json_encode($value);
                    }
                }
                $finalRules = [
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'address' => 'required|string',
                    'location' => 'required|string',
                    'price' => 'required|numeric|min:0',
                    'price_per_night' => 'required|numeric|min:0',
                    'currency' => 'required|string|size:3',
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'city' => 'required|string',
                    'country' => 'required|string',
                    'check_in_hour' => 'required|string',
                    'check_out_hour' => 'required|string',
                    'num_of_guests' => 'required|integer|min:1',
                    'maximum_guests' => 'required|integer|min:1',
                    'property_type_id' => 'required|exists:property_types,id',
                    'category_id' => 'required|exists:categories,id',
                    'host_type' => 'required|in:Private Individual,Business',
                    'num_of_bedrooms' => 'required|integer|min:1',
                    'num_of_bathrooms' => 'required|integer|min:1',
                    'nights' => 'nullable|integer|min:1',
                    'check_in_date' => 'nullable|date',
                    'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
                    'first_reserver' => 'required|string',
                    'listing_type' => 'nullable|string',
                    'host_id' => 'required|exists:users,host_id',
                ];
                $validator = Validator::make($property->toArray(), $finalRules);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors(), 'draft_id' => $property->id], 422);
                }
                $property->status = Property::STATUS_PUBLISHED;
                $property->version = ($property->version ?? 1) + 1;
                $property->save();
                $images = $property->images ? json_decode($property->images, true) : [];
                $completionPercentage = $this->calculateCompletionPercentage($property);
                $stateInfo = $this->getAllowedTransitions($property);
                $etag = $this->generateETag($property);
                return response()->json([
                    'success' => true, 
                    'property' => $property->toArray() + [
                        'images' => $images,
                        'completion_percentage' => $completionPercentage,
                        'state_management' => $stateInfo
                    ]
                ])->header('ETag', $etag);
            }

            // 6. Return the draft ID for the next step
            // Return images as array (decode JSON) and completion percentage
            $images = $property->images ? json_decode($property->images, true) : [];
            $completionPercentage = $this->calculateCompletionPercentage($property);
            $stateInfo = $this->getAllowedTransitions($property);
            $etag = $this->generateETag($property);
            
            // Get the listing with type-specific data
            $listing = Listing::where('property_id', $property->id)->first();
            $responseData = [
                'draft_id' => $property->id,
                'property' => $property->toArray() + [
                    'images' => $images,
                    'completion_percentage' => $completionPercentage,
                    'state_management' => $stateInfo
                ],
            ];

            // Include type-specific data if listing exists
            if ($listing) {
                $responseData['listing'] = [
                    'id' => $listing->id,
                    'listing_type' => $listing->listing_type,
                    'status' => $listing->status,
                    'is_completed' => $listing->is_completed
                ];

                // Add type-specific details
                if ($listing->listing_type === 'stay' && $listing->stay) {
                    $responseData['stay_details'] = $listing->stay->toArray();
                } elseif ($listing->listing_type === 'experience' && $listing->experience) {
                    $responseData['experience_details'] = $listing->experience->toArray();
                }
            }
            
            return response()->json($responseData)->header('ETag', $etag);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Finalize a wizard listing
     */
    public function finalizeListing(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            // Find the draft property
            $property = Property::where('id', $id)
                ->where('host_id', $user->id)
                ->where('status', Property::STATUS_DRAFT)
                ->first();

            if (!$property) {
                return response()->json([
                    'code' => 'DRAFT_NOT_FOUND',
                    'message' => 'Draft listing not found or already finalized'
                ], 404);
            }

            // Validate If-Match header for optimistic concurrency control
            $concurrencyError = $this->validateIfMatch($request, $property);
            if ($concurrencyError) {
                return $concurrencyError;
            }

            // Check if validate_only is requested
            $validateOnly = $request->boolean('validate_only', false);

            // Required fields for finalization
            $requiredFields = [
                'title', 'description', 'address', 'location', 'price', 'price_per_night',
                'currency', 'latitude', 'longitude', 'city', 'country', 'check_in_hour',
                'check_out_hour', 'num_of_guests', 'maximum_guests', 'property_type_id',
                'category_id', 'host_type', 'num_of_bedrooms', 'num_of_bathrooms',
                'first_reserver', 'host_id'
            ];

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($property->$field)) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return response()->json([
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Required fields are missing',
                    'field_errors' => array_fill_keys($missingFields, ['This field is required'])
                ], 422);
            }

            // If validate_only, return success without finalizing
            if ($validateOnly) {
                $completionPercentage = $this->calculateCompletionPercentage($property);
                $stateInfo = $this->getAllowedTransitions($property);
                return response()->json([
                    'code' => 'VALIDATION_SUCCESS',
                    'message' => 'Listing is ready for finalization',
                    'property' => $property->toArray() + [
                        'images' => $property->images ? json_decode($property->images, true) : [],
                        'completion_percentage' => $completionPercentage,
                        'state_management' => $stateInfo
                    ]
                ]);
            }

            // Finalize the listing
            $property->status = Property::STATUS_PUBLISHED;
            $property->version = ($property->version ?? 1) + 1;
            $property->save();

            // Create or update the listing record
            $listing = Listing::firstOrCreate(
                ['property_id' => $property->id],
                [
                    'host_id' => $user->id,
                    'listing_type' => $request->input('listing_type', 'stay'),
                    'status' => Listing::STATUS_PUBLISHED,
                    'is_completed' => true,
                    'published_at' => now()
                ]
            );

            // Update listing status to published
            $listing->update([
                'status' => Listing::STATUS_PUBLISHED,
                'is_completed' => true,
                'published_at' => now()
            ]);

            // Handle type-specific data during finalization
            $this->handleTypeSpecificData($request, $listing);

            $completionPercentage = $this->calculateCompletionPercentage($property);
            $stateInfo = $this->getAllowedTransitions($property);
            
            // Generate new ETag
            $etag = $this->generateETag($property);
            
            // Prepare response with type-specific data
            $responseData = [
                'code' => 'SUCCESS',
                'message' => 'Listing finalized successfully',
                'property' => $property->toArray() + [
                    'images' => $property->images ? json_decode($property->images, true) : [],
                    'completion_percentage' => $completionPercentage,
                    'state_management' => $stateInfo
                ],
                'listing' => [
                    'id' => $listing->id,
                    'listing_type' => $listing->listing_type,
                    'status' => $listing->status,
                    'is_completed' => $listing->is_completed,
                    'published_at' => $listing->published_at
                ]
            ];

            // Add type-specific details
            if ($listing->listing_type === 'stay' && $listing->stay) {
                $responseData['stay_details'] = $listing->stay->toArray();
            } elseif ($listing->listing_type === 'experience' && $listing->experience) {
                $responseData['experience_details'] = $listing->experience->toArray();
            }
            
            return response()->json($responseData)->header('ETag', $etag);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a draft listing
     */
    public function validateListing(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $property = Property::where('host_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$property) {
                return response()->json([
                    'code' => 'PROPERTY_NOT_FOUND',
                    'message' => 'Property not found'
                ], 404);
            }

            // Get validation type from request
            $validationType = $request->input('validation_type', 'full'); // 'step' or 'full'
            $step = $request->input('step'); // For step-by-step validation

            $errors = [];
            $warnings = [];

            if ($validationType === 'step' && $step) {
                // Step-by-step validation
                switch ($step) {
                    case 'basic_info':
                        if (!$property->title) $errors['title'] = 'Title is required';
                        if (!$property->description) $errors['description'] = 'Description is required';
                        if (!$property->property_type) $errors['property_type'] = 'Property type is required';
                        break;
                    case 'location':
                        if (!$property->address) $errors['address'] = 'Address is required';
                        if (!$property->city) $errors['city'] = 'City is required';
                        if (!$property->state) $errors['state'] = 'State is required';
                        if (!$property->country) $errors['country'] = 'Country is required';
                        if (!$property->latitude || !$property->longitude) $errors['coordinates'] = 'Location coordinates are required';
                        break;
                    case 'amenities':
                        if (!$property->bedrooms) $errors['bedrooms'] = 'Number of bedrooms is required';
                        if (!$property->bathrooms) $errors['bathrooms'] = 'Number of bathrooms is required';
                        if (!$property->max_guests) $errors['max_guests'] = 'Maximum guests is required';
                        break;
                    case 'pricing':
                        if (!$property->price_per_night) $errors['price_per_night'] = 'Price per night is required';
                        break;
                    case 'media':
                        $images = $property->images ? json_decode($property->images, true) : [];
                        if (empty($images)) $errors['images'] = 'At least one image is required';
                        if (count($images) < 3) $warnings['images'] = 'At least 3 images recommended for better visibility';
                        break;
                }
            } else {
                // Full validation
                if (!$property->title) $errors['title'] = 'Title is required';
                if (!$property->description) $errors['description'] = 'Description is required';
                if (!$property->property_type) $errors['property_type'] = 'Property type is required';
                if (!$property->address) $errors['address'] = 'Address is required';
                if (!$property->city) $errors['city'] = 'City is required';
                if (!$property->state) $errors['state'] = 'State is required';
                if (!$property->country) $errors['country'] = 'Country is required';
                if (!$property->latitude || !$property->longitude) $errors['coordinates'] = 'Location coordinates are required';
                if (!$property->bedrooms) $errors['bedrooms'] = 'Number of bedrooms is required';
                if (!$property->bathrooms) $errors['bathrooms'] = 'Number of bathrooms is required';
                if (!$property->max_guests) $errors['max_guests'] = 'Maximum guests is required';
                if (!$property->price_per_night) $errors['price_per_night'] = 'Price per night is required';
                
                $images = $property->images ? json_decode($property->images, true) : [];
                if (empty($images)) $errors['images'] = 'At least one image is required';
                if (count($images) < 3) $warnings['images'] = 'At least 3 images recommended for better visibility';
            }

            // Calculate completion percentage
            $totalFields = 11; // Total required fields
            $completedFields = 0;
            
            if ($property->title) $completedFields++;
            if ($property->description) $completedFields++;
            if ($property->property_type) $completedFields++;
            if ($property->address) $completedFields++;
            if ($property->city) $completedFields++;
            if ($property->state) $completedFields++;
            if ($property->country) $completedFields++;
            if ($property->latitude && $property->longitude) $completedFields++;
            if ($property->bedrooms) $completedFields++;
            if ($property->bathrooms) $completedFields++;
            if ($property->max_guests) $completedFields++;
            if ($property->price_per_night) $completedFields++;
            
            $images = $property->images ? json_decode($property->images, true) : [];
            if (!empty($images)) $completedFields++;
            
            $completionPercentage = round(($completedFields / ($totalFields + 1)) * 100); // +1 for images

            $response = [
                'code' => 'SUCCESS',
                'message' => empty($errors) ? 'Validation passed' : 'Validation failed',
                'is_valid' => empty($errors),
                'completion_percentage' => $completionPercentage,
                'validation_type' => $validationType
            ];

            if (!empty($errors)) {
                $response['field_errors'] = $errors;
            }

            if (!empty($warnings)) {
                $response['warnings'] = $warnings;
            }

            if ($validationType === 'step' && $step) {
                $response['step'] = $step;
            }

            return response()->json($response, empty($errors) ? 200 : 422);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update a wizard listing (PATCH for partial updates)
     */
    public function updateWizardListing(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $property = Property::where('host_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$property) {
                return response()->json([
                    'code' => 'PROPERTY_NOT_FOUND',
                    'message' => 'Property not found'
                ], 404);
            }

            // Only allow updates to draft properties
            if (!$property->isDraft()) {
                return response()->json([
                    'code' => 'PROPERTY_NOT_DRAFT',
                    'message' => 'Only draft properties can be updated through wizard'
                ], 400);
            }

            // Validate If-Match header for optimistic concurrency control
            $concurrencyError = $this->validateIfMatch($request, $property);
            if ($concurrencyError) {
                return $concurrencyError;
            }

            // Define allowed fields for partial updates
            $allowedFields = [
                'title', 'description', 'property_type', 'address', 'city', 'state', 
                'country', 'postal_code', 'latitude', 'longitude', 'bedrooms', 
                'bathrooms', 'max_guests', 'price_per_night', 'cleaning_fee',
                'security_deposit', 'check_in_time', 'check_out_time', 'house_rules',
                'cancellation_policy', 'amenities', 'images'
            ];

            // Only update fields that are present in the request
            $updateData = [];
            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field);
                    
                    // Handle special cases
                    if ($field === 'amenities' && is_array($value)) {
                        $updateData[$field] = json_encode($value);
                    } elseif ($field === 'images' && is_array($value)) {
                        $updateData[$field] = json_encode($value);
                    } else {
                        $updateData[$field] = $value;
                    }
                }
            }

            if (empty($updateData)) {
                return response()->json([
                    'code' => 'NO_UPDATES',
                    'message' => 'No valid fields provided for update'
                ], 400);
            }

            // Increment version for optimistic concurrency control
            $updateData['version'] = ($property->version ?? 1) + 1;

            // Update the property
            $property->update($updateData);
            $property->refresh();

            // Update or create listing record
            $listing = Listing::firstOrCreate(
                ['property_id' => $property->id],
                [
                    'host_id' => $user->id,
                    'listing_type' => $request->input('listing_type', 'stay'),
                    'status' => Listing::STATUS_DRAFT,
                    'is_completed' => false
                ]
            );

            // Update listing type if provided
            if ($request->has('listing_type')) {
                $listing->listing_type = $request->input('listing_type');
                $listing->save();
            }

            // Handle type-specific data
            $this->handleTypeSpecificData($request, $listing);

            // Calculate completion percentage
            $completionPercentage = $this->calculateCompletionPercentage($property);
            $stateInfo = $this->getAllowedTransitions($property);

            // Generate new ETag
            $etag = $this->generateETag($property);

            // Prepare response with type-specific data
            $responseData = [
                'code' => 'SUCCESS',
                'message' => 'Property updated successfully',
                'property' => array_merge($property->toArray(), [
                    'images' => $property->images ? json_decode($property->images, true) : [],
                    'amenities' => $property->amenities ? json_decode($property->amenities, true) : [],
                    'completion_percentage' => $completionPercentage,
                    'state_management' => $stateInfo
                ]),
                'listing' => [
                    'id' => $listing->id,
                    'listing_type' => $listing->listing_type,
                    'status' => $listing->status,
                    'is_completed' => $listing->is_completed
                ]
            ];

            // Add type-specific details
            if ($listing->listing_type === 'stay' && $listing->stay) {
                $responseData['stay_details'] = $listing->stay->toArray();
            } elseif ($listing->listing_type === 'experience' && $listing->experience) {
                $responseData['experience_details'] = $listing->experience->toArray();
            }
            
            return response()->json($responseData)->header('ETag', $etag);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate completion percentage for a property
     */
    private function calculateCompletionPercentage($property)
    {
        $requiredFields = [
            'title', 'description', 'property_type', 'address', 'city', 'state',
            'country', 'latitude', 'longitude', 'bedrooms', 'bathrooms', 
            'max_guests', 'price_per_night'
        ];
        
        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($property->$field)) {
                $completedFields++;
            }
        }
        
        // Check for images
        $images = $property->images ? json_decode($property->images, true) : [];
        if (!empty($images)) {
            $completedFields++;
        }
        
        $totalFields = count($requiredFields) + 1; // +1 for images
        return round(($completedFields / $totalFields) * 100);
    }

    /**
     * Get allowed transitions for a property based on its current state
     */
    private function getAllowedTransitions($property)
    {
        $completionPercentage = $this->calculateCompletionPercentage($property);
        $transitions = [];

        if ($property->isDraft()) {
            // Draft state transitions
            $transitions[] = [
                'action' => 'update',
                'method' => 'PATCH',
                'endpoint' => '/api/v1/wizard-listings/' . $property->id,
                'description' => 'Update draft property details'
            ];
            
            $transitions[] = [
                'action' => 'validate',
                'method' => 'POST',
                'endpoint' => '/api/v1/wizard-listings/' . $property->id . '/validate',
                'description' => 'Validate property completeness'
            ];

            if ($completionPercentage >= 80) {
                $transitions[] = [
                    'action' => 'finalize',
                    'method' => 'POST',
                    'endpoint' => '/api/v1/wizard-listings/' . $property->id . '/finalize',
                    'description' => 'Finalize and publish property',
                    'requirements' => ['completion_percentage >= 80']
                ];
            }

            $transitions[] = [
                'action' => 'delete',
                'method' => 'DELETE',
                'endpoint' => '/api/v1/listings/' . $property->id,
                'description' => 'Delete draft property'
            ];
        } else {
            // Published state transitions
            $transitions[] = [
                'action' => 'update',
                'method' => 'POST',
                'endpoint' => '/api/v1/listings/' . $property->id,
                'description' => 'Update published property details'
            ];

            $transitions[] = [
                'action' => 'deactivate',
                'method' => 'POST',
                'endpoint' => '/api/v1/listings/' . $property->id . '/deactivate',
                'description' => 'Temporarily deactivate property'
            ];

            $transitions[] = [
                'action' => 'delete',
                'method' => 'DELETE',
                'endpoint' => '/api/v1/listings/' . $property->id,
                'description' => 'Permanently delete property'
            ];
        }

        return [
            'current_state' => $property->isDraft() ? 'draft' : 'published',
            'completion_percentage' => $completionPercentage,
            'allowed_transitions' => $transitions
        ];
    }

    /**
     * Generate ETag for a property based on its version
     */
    private function generateETag($property)
    {
        $version = $property->version ?? 1;
        return '"' . md5($property->id . '-' . $version . '-' . $property->updated_at) . '"';
    }

    /**
     * Validate If-Match header for optimistic concurrency control
     */
    private function validateIfMatch(Request $request, $property)
    {
        $ifMatch = $request->header('If-Match');
        if ($ifMatch) {
            $currentETag = $this->generateETag($property);
            if ($ifMatch !== $currentETag) {
                return response()->json([
                    'code' => 'PRECONDITION_FAILED',
                    'message' => 'Resource has been modified by another request',
                    'current_etag' => $currentETag
                ], 412);
            }
        }
        return null;
    }

    /**
     * Update a host's listing
     */
    public function updateHostListing(Request $request, $listingId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $listing = Listing::where('host_id', $user->id)
                ->find($listingId);

            if (!$listing) {
                return response()->json([
                    'code' => 'LISTING_NOT_FOUND',
                    'message' => 'Listing not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
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
                'status' => 'sometimes|string|in:' . implode(',', Listing::getAvailableStatuses()),
                'cancellation_policy' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Validation failed',
                    'field_errors' => $validator->errors()
                ], 422);
            }

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

            // Handle image uploads if new images are provided
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
                                'Key'        => 'properties/' . $fileName,
                                'SourceFile' => $image->getPathname(),
                            ]);

                            if (isset($result['ObjectURL'])) {
                                $imagePaths[] = $result['ObjectURL'];
                            } else {
                                throw new \Exception('Object URL not returned from Wasabi');
                            }
                        } catch (\Exception $e) {
                            // Fallback to local storage
                            $localPath = $image->storeAs('properties', $fileName, 'public');
                            $localUrl = \Storage::disk('public')->url($localPath);
                            $imagePaths[] = $localUrl;
                        }
                    }
                }
            }

            // Update the listing
            $listing->update([
                'title' => $request->input('title', $listing->title),
                'category_id' => $request->has('category_id') ? $request->category_id[0] : $listing->category_id,
                'status' => $request->has('status') ? $request->status : $listing->status,
                'cancellation_policy' => $request->has('cancellation_policy') ? $request->cancellation_policy : $listing->cancellation_policy
            ]);

            DB::commit();

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Listing updated successfully',
                'data' => [
                    'property' => $property,
                    'listing' => $listing
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all listings for a host with pagination and filters
     */
    public function fetchHostListings(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            // Get both published listings and draft properties
            $listingsQuery = Listing::where('host_id', $user->id)
                ->with([
                    'propertyType',
                    'reviews',
                    'property',
                    'stay',
                    'experience'
                ]);

            $draftsQuery = Property::where('user_id', $user->id)
                ->where('status', Property::STATUS_DRAFT)
                ->with(['propertyType', 'reviews']);

            // Add listing_type filter if provided (only for listings, not drafts)
            if ($request->has('listing_type')) {
                $listingType = $request->listing_type;
                $listingsQuery->where('listing_type', $listingType);
                // Note: drafts don't have listing_type in properties table
            }

            // Add status filter if provided (only applies to published listings)
            if ($request->has('status')) {
                $listingsQuery->where('status', $request->status);
            }

            // Add search filter if provided
            if ($request->has('search')) {
                $search = $request->search;
                $listingsQuery->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('property', function($q) use ($search) {
                          $q->where('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%");
                      });
                });

                $draftsQuery->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
                });
            }

            // Get the results
            $listings = $listingsQuery->get();
            $drafts = $draftsQuery->get();

            // Transform listings to include status and listing_type
            $transformedListings = $listings->map(function ($listing) {
                $data = $listing->toArray();
                $data['status'] = $listing->status;
                $data['listing_type'] = $listing->listing_type;
                $data['status'] = Property::STATUS_PUBLISHED;
                
                // Add type-specific data
                if ($listing->listing_type === 'stay' && $listing->stay) {
                    $data['stay_details'] = $listing->stay->toArray();
                }
                if ($listing->listing_type === 'experience' && $listing->experience) {
                    $data['experience_details'] = $listing->experience->toArray();
                }
                
                return $data;
            });

            // Transform drafts to include status and listing_type
            $transformedDrafts = $drafts->map(function ($property) {
                $data = $property->toArray();
                $data['status'] = 'draft';
                $data['listing_type'] = $property->listing_type;
                $data['status'] = Property::STATUS_DRAFT;
                $data['completion_percentage'] = $this->calculateCompletionPercentage($property);
                $data['images'] = $property->images ? json_decode($property->images, true) : [];
                $data['amenities'] = $property->amenities ? json_decode($property->amenities, true) : [];
                return $data;
            });

            // Combine and sort results
            $allResults = $transformedListings->concat($transformedDrafts);
            
            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            $sortedResults = $allResults->sortBy(function ($item) use ($sortBy) {
                return $item[$sortBy] ?? $item['created_at'];
            });
            
            if ($sortOrder === 'desc') {
                $sortedResults = $sortedResults->reverse();
            }

            // Manual pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $total = $sortedResults->count();
            $items = $sortedResults->forPage($page, $perPage)->values();

            $paginatedData = [
                'current_page' => $page,
                'data' => $items,
                'first_page_url' => $request->url() . '?page=1',
                'from' => ($page - 1) * $perPage + 1,
                'last_page' => ceil($total / $perPage),
                'last_page_url' => $request->url() . '?page=' . ceil($total / $perPage),
                'next_page_url' => $page < ceil($total / $perPage) ? $request->url() . '?page=' . ($page + 1) : null,
                'path' => $request->url(),
                'per_page' => $perPage,
                'prev_page_url' => $page > 1 ? $request->url() . '?page=' . ($page - 1) : null,
                'to' => min($page * $perPage, $total),
                'total' => $total
            ];

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Host listings fetched successfully',
                'data' => $paginatedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch host's draft listings
     */
    public function fetchHostDraftListings(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $query = Property::where('host_id', $user->id)
                ->where('status', Property::STATUS_DRAFT)
                ->with([
                    'propertyType',
                    'reviews'
                ]);

            // Add search filter if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
                });
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->input('per_page', 10);
            $drafts = $query->paginate($perPage);

            // Add completion percentage, state management, and ETag to each draft
            $drafts->getCollection()->transform(function ($property) {
                $completionPercentage = $this->calculateCompletionPercentage($property);
                $stateInfo = $this->getAllowedTransitions($property);
                $etag = $this->generateETag($property);
                $propertyArray = $property->toArray();
                $propertyArray['completion_percentage'] = $completionPercentage;
                $propertyArray['images'] = $property->images ? json_decode($property->images, true) : [];
                $propertyArray['amenities'] = $property->amenities ? json_decode($property->amenities, true) : [];
                $propertyArray['state_management'] = $stateInfo;
                $propertyArray['etag'] = $etag;
                return $propertyArray;
            });

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Host draft listings fetched successfully',
                'data' => $drafts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a host's listing and all related data
     */
    public function deleteHostListing($listingId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $listing = Listing::where('host_id', $user->id)
                ->with(['property', 'bookings'])
                ->find($listingId);

            if (!$listing) {
                return response()->json([
                    'code' => 'LISTING_NOT_FOUND',
                    'message' => 'Listing not found'
                ], 404);
            }

            // Check if there are any active bookings
            $activeBookings = $listing->bookings()
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->exists();

            if ($activeBookings) {
                return response()->json([
                    'code' => 'ACTIVE_BOOKINGS_EXIST',
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
                    'code' => 'SUCCESS',
                    'message' => 'Listing and all related data deleted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $e->getMessage()
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
    public function fetchAllListings(Request $request)
    {
        try {
            // Get filter and pagination parameters
            $propertyType = strtolower($request->query('property_type', '')) ?: null; // e.g., 'bangalow', 'apartment', etc.
            $listingType = strtolower($request->query('listing_type', '')) ?: null; // e.g., 'experience' or 'stay'
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 10);

            // Build the query with relationships
            $query = Listing::with([
                'property',
                'propertyType',
                'host',
                'reviews'
            ]);

            // Filter by listing_type if provided
            if ($listingType) {
                $query->whereRaw('LOWER(listing_type) = ?', [$listingType]);
            }

            // Filter by property_type (string name) if provided
            if ($propertyType) {
                $query->whereHas('property.propertyType', function ($q) use ($propertyType) {
                    $q->whereRaw('LOWER(name) = ?', [$propertyType]);
                });
            }

            // Paginate the results
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
            $listings = $paginator->items();

            // Get user's favorite listings if user is authenticated
            $userFavorites = [];
            $userCurrency = 'USD'; // Default currency

            if (auth()->check()) {
                $userFavorites = Favorite::where('user_id', auth()->id())
                    ->pluck('property_id')
                    ->toArray();
                $userCurrency = auth()->user()->currency ?? 'USD';
            } else {
                $userCurrency = \App\Helpers\GeoLocationHelper::getCurrencyFromIP();
            }

            // Transform the response
            $listings = collect($listings)->map(function ($listing) use ($userFavorites, $userCurrency) {
                $property = $listing->property;
                $price = $property ? $property->price : null;
                $pricePerNight = $property ? $property->price_per_night : null;
                $additionalGuestPrice = $property ? $property->additional_guest_price : null;
                $childrenPrice = $property ? $property->children_price : null;
                $propertyCurrency = $property ? $property->currency : 'USD';
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
                $images = $property && isset($property->images) ? $property->images : [];
                $amenities = $property && isset($property->amenities) ? json_decode($property->amenities, true) : [];
                $place_items = $property && isset($property->place_items) ? json_decode($property->place_items, true) : [];
                $listing_favoutites = $property && isset($property->favourites) ? json_decode($property->favourites, true) : [];
                $safety_items = $property && isset($property->safety_items) ? json_decode($property->safety_items, true) : [];
                $who_is_there = $property && isset($property->who_is_there) ? json_decode($property->who_is_there, true) : [];
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
                    'featured_status' => $property ? $property->featured_status : null,
                    'is_favorite' => in_array($listing->property_id, $userFavorites),
                    'images' => $images,
                    'amenities' => $amenities,
                    'place_items' => $place_items,
                    'listing_favoutites' => $listing_favoutites,
                    'safety_items' => $safety_items,
                    'who_is_there' => $who_is_there,
                    'property_type' => $property && $property->propertyType ? $property->propertyType : null,
                    'listing_type' => $listing->listing_type ? $listing->listing_type : null,
                    'host' => $listing->host ? [
                        'id' => $listing->host->id,
                        'name' => $listing->host->fname . ' ' . $listing->host->lname,
                        'email' => $listing->host->email,
                        'phone' => $listing->host->phone
                    ] : null,
                    'reviews' => collect($listing->reviews)->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'rating' => $review->rating,
                            'comment' => $review->comment,
                            'created_at' => $review->created_at
                        ];
                    }),
                    'created_at' => $listing->created_at,
                    'updated_at' => $listing->updated_at,
                    // Add property availability
                    'availability' => $property ? $property->availability : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Listings fetched successfully',
                'data' => $listings,
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wizard metadata
     */
    public function getWizardMetadata()
    {
        try {
            $metadata = [
                'property_types' => [
                    ['value' => 'apartment', 'label' => 'Apartment', 'description' => 'A self-contained housing unit within a larger building'],
                    ['value' => 'house', 'label' => 'House', 'description' => 'A standalone residential building'],
                    ['value' => 'condo', 'label' => 'Condominium', 'description' => 'A privately owned unit in a building or complex'],
                    ['value' => 'townhouse', 'label' => 'Townhouse', 'description' => 'A multi-floor home that shares walls with adjacent units'],
                    ['value' => 'villa', 'label' => 'Villa', 'description' => 'A luxurious house, often with gardens'],
                    ['value' => 'studio', 'label' => 'Studio', 'description' => 'A single room that combines living, sleeping, and kitchen areas'],
                    ['value' => 'loft', 'label' => 'Loft', 'description' => 'A large, open space converted from industrial or commercial use']
                ],
                'categories' => [
                    ['value' => 'entire_place', 'label' => 'Entire Place', 'description' => 'Guests have the whole place to themselves'],
                    ['value' => 'private_room', 'label' => 'Private Room', 'description' => 'Guests have a private room but may share common areas'],
                    ['value' => 'shared_room', 'label' => 'Shared Room', 'description' => 'Guests share a room with others']
                ],
                'amenities' => [
                    // Basic amenities
                    ['value' => 'wifi', 'label' => 'WiFi', 'category' => 'basic', 'icon' => 'wifi'],
                    ['value' => 'kitchen', 'label' => 'Kitchen', 'category' => 'basic', 'icon' => 'kitchen'],
                    ['value' => 'washer', 'label' => 'Washer', 'category' => 'basic', 'icon' => 'washer'],
                    ['value' => 'dryer', 'label' => 'Dryer', 'category' => 'basic', 'icon' => 'dryer'],
                    ['value' => 'air_conditioning', 'label' => 'Air Conditioning', 'category' => 'basic', 'icon' => 'ac'],
                    ['value' => 'heating', 'label' => 'Heating', 'category' => 'basic', 'icon' => 'heating'],
                    
                    // Entertainment
                    ['value' => 'tv', 'label' => 'TV', 'category' => 'entertainment', 'icon' => 'tv'],
                    ['value' => 'netflix', 'label' => 'Netflix', 'category' => 'entertainment', 'icon' => 'netflix'],
                    ['value' => 'sound_system', 'label' => 'Sound System', 'category' => 'entertainment', 'icon' => 'sound'],
                    
                    // Safety & Security
                    ['value' => 'smoke_alarm', 'label' => 'Smoke Alarm', 'category' => 'safety', 'icon' => 'smoke_alarm'],
                    ['value' => 'carbon_monoxide_alarm', 'label' => 'Carbon Monoxide Alarm', 'category' => 'safety', 'icon' => 'co_alarm'],
                    ['value' => 'fire_extinguisher', 'label' => 'Fire Extinguisher', 'category' => 'safety', 'icon' => 'fire_extinguisher'],
                    ['value' => 'first_aid_kit', 'label' => 'First Aid Kit', 'category' => 'safety', 'icon' => 'first_aid'],
                    
                    // Outdoor
                    ['value' => 'pool', 'label' => 'Pool', 'category' => 'outdoor', 'icon' => 'pool'],
                    ['value' => 'hot_tub', 'label' => 'Hot Tub', 'category' => 'outdoor', 'icon' => 'hot_tub'],
                    ['value' => 'balcony', 'label' => 'Balcony', 'category' => 'outdoor', 'icon' => 'balcony'],
                    ['value' => 'garden', 'label' => 'Garden', 'category' => 'outdoor', 'icon' => 'garden'],
                    ['value' => 'bbq_grill', 'label' => 'BBQ Grill', 'category' => 'outdoor', 'icon' => 'bbq'],
                    
                    // Parking & Transportation
                    ['value' => 'free_parking', 'label' => 'Free Parking', 'category' => 'parking', 'icon' => 'parking'],
                    ['value' => 'paid_parking', 'label' => 'Paid Parking', 'category' => 'parking', 'icon' => 'paid_parking'],
                    ['value' => 'ev_charger', 'label' => 'EV Charger', 'category' => 'parking', 'icon' => 'ev_charger']
                ],
                'constraints' => [
                    'bedrooms' => ['min' => 0, 'max' => 20],
                    'bathrooms' => ['min' => 0, 'max' => 10, 'step' => 0.5],
                    'max_guests' => ['min' => 1, 'max' => 50],
                    'price_per_night' => ['min' => 1, 'max' => 10000, 'currency' => 'USD'],
                    'title' => ['min_length' => 10, 'max_length' => 100],
                    'description' => ['min_length' => 50, 'max_length' => 2000],
                    'images' => ['min_count' => 1, 'max_count' => 20, 'max_size_mb' => 10],
                    'amenities' => ['min_count' => 0, 'max_count' => 50]
                ],
                'validation_rules' => [
                    'required_fields' => [
                        'title', 'description', 'property_type', 'category',
                        'bedrooms', 'bathrooms', 'max_guests', 'price_per_night',
                        'address', 'city', 'state', 'country', 'postal_code',
                        'latitude', 'longitude'
                    ],
                    'recommended_fields' => [
                        'images', 'amenities', 'house_rules', 'check_in_time', 'check_out_time'
                    ]
                ]
            ];

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Wizard metadata retrieved successfully',
                'data' => $metadata
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Handle type-specific data for stays and experiences
     */
    private function handleTypeSpecificData(Request $request, Listing $listing)
    {
        if ($listing->listing_type === 'stay') {
            $this->handleStayData($request, $listing);
        } elseif ($listing->listing_type === 'experience') {
            $this->handleExperienceData($request, $listing);
        }
    }

    /**
     * Handle stay-specific data
     */
    private function handleStayData(Request $request, Listing $listing)
    {
        $stayFields = [
            'cleaning_fee', 'minimum_nights', 'maximum_nights', 'check_in_instructions',
            'self_check_in', 'keypad_code', 'lockbox_location', 'check_in_start_time',
            'check_in_end_time', 'check_out_time', 'quiet_hours_start', 'quiet_hours_end',
            'house_manual', 'wifi_name', 'wifi_password', 'parking_instructions',
            'local_recommendations'
        ];

        $stayData = [];
        foreach ($stayFields as $field) {
            if ($request->has($field)) {
                $stayData[$field] = $request->input($field);
            }
        }

        if (!empty($stayData)) {
            $listing->stay()->updateOrCreate(
                ['listing_id' => $listing->id],
                $stayData
            );
        }
    }

    /**
     * Handle experience-specific data
     */
    private function handleExperienceData(Request $request, Listing $listing)
    {
        $experienceFields = [
            'duration_hours', 'duration_minutes', 'minimum_group_size', 'maximum_group_size',
            'activity_type', 'difficulty_level', 'minimum_age', 'maximum_age',
            'physical_requirements', 'what_to_bring', 'meeting_point', 'meeting_instructions',
            'cancellation_hours', 'weather_dependent', 'languages_offered', 'includes',
            'excludes', 'safety_requirements', 'experience_highlights', 'itinerary',
            'price_per_person', 'group_discount_threshold', 'group_discount_percentage'
        ];

        $experienceData = [];
        foreach ($experienceFields as $field) {
            if ($request->has($field)) {
                $experienceData[$field] = $request->input($field);
            }
        }

        if (!empty($experienceData)) {
            $listing->experience()->updateOrCreate(
                ['listing_id' => $listing->id],
                $experienceData
            );
        }
    }
}
