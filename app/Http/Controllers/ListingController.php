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
}
