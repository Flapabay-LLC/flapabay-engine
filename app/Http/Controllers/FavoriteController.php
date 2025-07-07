<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Wishlist;
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
        // dd($request);
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'property_id' => 'nullable|integer|exists:properties,id',
                'is_default' => 'nullable|boolean',
            ]);

            $userId = auth()->user()->id;
            $isDefault = $request->boolean('is_default');

            // If is_default is true, unset all other default wishlists for this user
            if ($isDefault) {
                Wishlist::where('user_id', $userId)->update(['is_default' => false]);
            }

            $wishlist = Wishlist::create([
                'user_id' => $userId,
                'name' => $request->name,
                'is_default' => $isDefault,
            ]);

            // If property_id is provided, add it as a favorite
            if ($wishlist && $request->filled('property_id')) {
                Favorite::create([
                    'user_id' => $userId,
                    'property_id' => $request->property_id,
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
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $user = $request->user();

        // 1. Try to find the user's default wishlist
        $wishlist = \App\Models\Wishlist::where('user_id', auth()->user()->id)
            ->where('is_default', true)
            ->first();

        // 2. If no default, check if the user has exactly one wishlist
        if (!$wishlist) {
            $wishlists = \App\Models\Wishlist::where('user_id', auth()->user()->id)->get();
            if ($wishlists->count() === 1) {
                $wishlist = $wishlists->first();
                $wishlist->is_default = true;
                $wishlist->save();
            }
        }

        // 3. If still no wishlist, return empty response
        if (!$wishlist) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No wishlist available for this user',
                'favorite' => null,
            ], 200);
        }

        // Check if favorite already exists in this wishlist
        $existingFavorite = \App\Models\Favorite::where('user_id', auth()->user()->id)
            ->where('property_id', $request->property_id)
            ->where('wishlist_id', $wishlist->id)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'This property is already in your favorites'
            ], 409);
        }

        $favorite = \App\Models\Favorite::create([
            'user_id' => auth()->user()->id,
            'property_id' => $request->property_id,
            'wishlist_id' => $wishlist->id,
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
                ->where('user_id', auth()->user()->id)
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

    /**
     * Set a user's default wishlist (overriding any current default)
     */
    public function setDefaultWishlist(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'wishlist_id' => 'required|integer|exists:wishlists,id',
        ]);

        $wishlist = Wishlist::where('id', $request->wishlist_id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wishlist not found or does not belong to user',
            ], 404);
        }

        // Unset all user's default wishlists
        Wishlist::where('user_id', auth()->user()->id)->update(['is_default' => false]);
        // Set the selected wishlist as default
        $wishlist->is_default = true;
        $wishlist->save();

        return response()->json([
            'success' => true,
            'message' => 'Default wishlist set successfully',
            'wishlist' => $wishlist,
        ]);
    }
}
