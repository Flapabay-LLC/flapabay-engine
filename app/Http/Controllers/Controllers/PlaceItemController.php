<?php

namespace App\Http\Controllers;

use App\Models\PlaceItem;
use App\Http\Requests\StorePlaceItemRequest;
use App\Http\Requests\UpdatePlaceItemRequest;

class PlaceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
                'message' => 'Failed to fetch Place items',
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
    public function store(StorePlaceItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PlaceItem $placeItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlaceItem $placeItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlaceItemRequest $request, PlaceItem $placeItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlaceItem $placeItem)
    {
        //
    }
}
