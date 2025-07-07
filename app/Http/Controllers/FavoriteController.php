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
    public function getUserFavorites(Request $request)
    {
        try {
            // Validate wishlist parameter if provided
            if ($request->has('wishlist')) {
                $request->validate([
                    'wishlist' => 'required|exists:wishlists,id'
                ]);
            }

            $query = Favorite::with('property')
            ->where('user_id', auth()->user()->id);

            // Filter by wishlist if provided
            if ($request->has('wishlist')) {
                $query->where('wishlist_id', $request->wishlist);
            }

            $favorites = $query->get();

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
     * Create a wishlist for a user and add property_ids to it
     */
    public function createWishlist(Request $request)
    {
        // dd('here');
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255|unique:wishlists,name,NULL,id,user_id,' . $request->user_id,
                'property_ids' => 'nullable|array',
                'property_ids.*' => 'nullable|exists:properties,id',
            ]);

            $wishlist = \App\Models\Wishlist::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
            ]);

            $propertyIds = array_filter($request->property_ids ?? [], function($id) {
                return !is_null($id) && $id !== '';
            });
            foreach ($propertyIds as $propertyId) {
                \App\Models\Favorite::create([
                    'user_id' => $request->user_id,
                    'property_id' => $propertyId,
                    'wishlist_id' => $wishlist->id,
                ]);
            }

            $wishlist->load('favorites');
            return response()->json([
                'success' => true,
                'wishlist' => $wishlist,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create wishlist',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a favorite (add property to wishlist or create new favorite)
     */
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'property_id' => 'required|exists:properties,id',
            'wishlist_id' => 'required|exists:wishlists,id',
        ]);

        // Check if favorite already exists
        $existingFavorite = \App\Models\Favorite::where('user_id', $request->user_id)
            ->where('property_id', $request->property_id)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'This property is already in your favorites'
            ], 409);
        }

        $favorite = \App\Models\Favorite::create([
            'user_id' => $request->user_id,
            'property_id' => $request->property_id,
            'wishlist_id' => $request->wishlist_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Property added to favorites successfully',
            'favorite' => $favorite,
        ]);
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
        try {
            $request->validate([
                'property_id' => 'required|exists:properties,id',
                'wslist' => 'required|exists:wishlists,id'
            ]);

            $favorite = Favorite::where('user_id', auth()->user()->id)
                ->where('property_id', $request->property_id)
                ->where('wishlist_id', $request->wslist)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Property is not in this wishlist'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Property removed from wishlist successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove property from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all wishlists for the authenticated user, with properties and listings
     */
    public function myWishlists(Request $request)
    {
        $user = $request->user();
        try {
            $wishlists = \App\Models\Wishlist::with(['favorites.property.listing'])
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'My wishlists fetched successfully',
                'data' => $wishlists
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlists',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
