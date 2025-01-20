<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function getLocations()
    {
        // Fetch all locations from the database
        $locations = Location::all();

        // Return the locations as a JSON response
        return response()->json($locations);
    }

    /**
     * Create a new location.
     */
    public function createLocation(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'placeholder_img' => 'nullable|string',
        ]);

        // Create a new location with the validated data
        $location = Location::create($validatedData);

        // Return a success response with the newly created location
        return response()->json(['message' => 'Location created successfully', 'location' => $location], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        //
    }
}
