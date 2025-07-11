<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use Illuminate\Http\Request;

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
            // Require host_id in every request
            $hostId = $request->input('host_id');
            if (!$hostId) {
                return response()->json(['errors' => ['host_id' => ['The host_id field is required.']]], 422);
            }
            // Validate host_id exists
            $hostValidator = Validator::make(['host_id' => $hostId], [
                'host_id' => 'required|exists:users,host_id',
            ]);
            if ($hostValidator->fails()) {
                return response()->json(['errors' => $hostValidator->errors()], 422);
            }

            // 1. Find or create a draft property for this host
            $draftId = $request->input('draft_id');
            $property = null;
            if ($draftId) {
                $property = Property::where('id', $draftId)
                    ->where('host_id', $hostId)
                    ->where('is_draft', true)
                    ->first();
            }
            if (!$property) {
                // If no draft_id, try to find an existing draft for this host
                $property = Property::where('host_id', $hostId)
                    ->where('is_draft', true)
                    ->first();
            }
            if (!$property) {
                // Only create a new draft if none exists for this host
                $property = new Property(['is_draft' => true, 'host_id' => $hostId]);
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

            // 3. Update the draft with the new fields and host_id if not set
            $property->fill($request->only(array_keys($fieldsToValidate)));
            if (!$property->host_id) {
                $property->host_id = $hostId;
            }
            $property->is_draft = true;
            $property->save();

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
                $property->is_draft = false;
                $property->save();
                $images = $property->images ? json_decode($property->images, true) : [];
                return response()->json(['success' => true, 'property' => $property->toArray() + ['images' => $images]]);
            }

            // 6. Return the draft ID for the next step
            // Return images as array (decode JSON)
            $images = $property->images ? json_decode($property->images, true) : [];
            return response()->json([
                'draft_id' => $property->id,
                'property' => $property->toArray() + ['images' => $images],
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
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

        // dd(auth()->user());
        try {
            $query = Listing::where('host_id', auth()->user()->host_id)
                ->with([
                    'propertyType',
                    // 'amenities',
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
            $listing = Listing::where('host_id', auth()->user()->host_id)
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
}
