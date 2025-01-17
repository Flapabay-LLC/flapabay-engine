<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Property;
use Illuminate\Http\Request;

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
    public function search(Request $request){
        // Extract filters from the request
        $categoryIds = $request->input('category_id', []);
        $propertyTypeIds = $request->input('property_type_id', []);
        $keyword = $request->input('keyword', '');

        // Build query with filters
        $query = Property::query();

        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $query->orWhereRaw("JSON_CONTAINS(category_id, '\"$categoryId\"')");
            }
        }

        if (!empty($propertyTypeIds)) {
            foreach ($propertyTypeIds as $propertyTypeId) {
                $query->orWhereRaw("JSON_CONTAINS(property_type_id, '\"$propertyTypeId\"')");
            }
        }

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->orWhere('title', 'LIKE', "%{$keyword}%")
                ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        // Paginate the results
        $properties = $query->paginate(10);

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
}
