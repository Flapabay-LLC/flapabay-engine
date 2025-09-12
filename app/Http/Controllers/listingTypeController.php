<?php

namespace App\Http\Controllers;

use App\Models\listingType;
use App\Http\Requests\StorelistingTypeRequest;
use App\Http\Requests\UpdatelistingTypeRequest;
use Illuminate\Http\Request;

class listingTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = listingType::query();

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $listingTypes = $query->get();

            return response()->json([
                'status' => 'success',
                'message' => 'listing types fetched successfully',
                'data' => $listingTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch listing types',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function store(StorelistingTypeRequest $request)
    {
        try {
            $listingType = listingType::create($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'listing type created successfully',
                'data' => $listingType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create listing type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(listingType $listingType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(listingType $listingType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatelistingTypeRequest $request, listingType $listingType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(listingType $listingType)
    {
        //
    }
}
