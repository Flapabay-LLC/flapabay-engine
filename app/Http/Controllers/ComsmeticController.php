<?php

namespace App\Http\Controllers;

use App\Models\Comsmetic;
use App\Http\Requests\StoreComsmeticRequest;
use App\Http\Requests\UpdateComsmeticRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComsmeticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getIcons()
    {
        // Retrieve all records from the 'icons' table
        $icons = DB::table('icons')->get();

        // Return the icons as a JSON response
        return response()->json($icons);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function createIcon(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'black_icon' => 'required|string|max:255',
            'white_icon' => 'required|string|max:255',
            'svg' => 'nullable|string',
            'icon_image_url' => 'nullable|string|url',
        ]);

        // Insert the validated data into the 'icons' table
        $icon = DB::table('icons')->insert([
            'black_icon' => $validatedData['black_icon'],
            'white_icon' => $validatedData['white_icon'],
            'svg' => $validatedData['svg'],
            'icon_image_url' => $validatedData['icon_image_url'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Return a success response
        return response()->json(['message' => 'Icon created successfully', 'icon' => $icon], 201);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComsmeticRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Comsmetic $comsmetic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comsmetic $comsmetic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateComsmeticRequest $request, Comsmetic $comsmetic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comsmetic $comsmetic)
    {
        //
    }
}
