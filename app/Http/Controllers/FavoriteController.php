<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Requests\UpdateFavoriteRequest;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $favorites = Favorite::with(['property', 'user'])->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Favorites fetched successfully',
                'data' => $favorites
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's favorites
     */
    public function getUserFavorites($userId)
    {
        try {
            $favorites = Favorite::with(['property' => function($query) {
                // $query->with(['images', 'amenities']);
            }])
            ->where('user_id', $userId)
            ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'User favorites fetched successfully',
                'data' => $favorites
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch user favorites',
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
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'property_id' => 'required|exists:properties,id'
            ]);

            // Check if favorite already exists
            $existingFavorite = Favorite::where('user_id', $request->user_id)
                ->where('property_id', $request->property_id)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Property is already in favorites'
                ], 400);
            }

            $favorite = Favorite::create([
                'user_id' => $request->user_id,
                'property_id' => $request->property_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Property added to favorites successfully',
                'data' => $favorite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add property to favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Favorite $favorite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favorite $favorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFavoriteRequest $request, Favorite $favorite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // dd($request);
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'property_id' => 'required|exists:properties,id'
            ]);

            $favorite = Favorite::where('user_id', $request->user_id)
                ->where('property_id', $request->property_id)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Property is not in favorites'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Property removed from favorites successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove property from favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
