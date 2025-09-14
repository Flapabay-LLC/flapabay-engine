<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;

class ExperienceController extends Controller
{
    /**
     * Display a listing of host's experiences.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user is a host
            if (!$user->is_host) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage experiences'
                ], 403);
            }

            // Get experience listing type ID
            $experienceTypeId = \App\Models\ListingType::where('name', 'experience')->first()?->id;
            if (!$experienceTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience listing type not found'
                ], 404);
            }

            $query = Listing::where('user_id', $user->id)
                ->where('listing_type_id', $experienceTypeId)
                ->with(['experience', 'listingType']);

            // Add search filter if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
                });
            }

            // Add status filter
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->input('per_page', 10);
            $experiences = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Experiences fetched successfully',
                'data' => $experiences
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch experiences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch experiences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created experience.
     */
    public function store(StoreExperienceRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user is a host
            if (!$user->is_host) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage experiences'
                ], 403);
            }

            // Get experience listing type ID
            $experienceTypeId = \App\Models\ListingType::where('name', 'experience')->first()?->id;
            if (!$experienceTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience listing type not found'
                ], 404);
            }

            DB::beginTransaction();

            // Create the main listing
            $listing = Listing::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'currency' => $request->currency ?? 'USD',
                'address' => $request->address,
                'location' => $request->city,
                'county' => $request->state,
                'country' => $request->country,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'listing_type_id' => $experienceTypeId,
                'status' => $request->status ?? 'draft',
                'images' => json_encode($request->images ?? []),
                'amenities' => json_encode($request->amenities ?? [])
            ]);

            // Create the experience-specific data
            $experience = Experience::create([
                'listing_id' => $listing->id,
                'duration' => $request->duration,
                'activity_type' => $request->activity_type,
                'group_size' => $request->group_size,
                'difficulty_level' => $request->difficulty_level ?? 'beginner',
                'included_items' => json_encode($request->included_items ?? []),
                'requirements' => $request->requirements,
                'cancellation_policy' => $request->cancellation_policy ?? 'flexible'
            ]);

            DB::commit();

            // Load the created experience with listing
            $listing->load('experience');

            return response()->json([
                'success' => true,
                'message' => 'Experience created successfully',
                'data' => $listing
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create experience: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified experience.
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user is a host
            if (!$user->is_host) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage experiences'
                ], 403);
            }

            // Get experience listing type ID
            $experienceTypeId = \App\Models\ListingType::where('name', 'experience')->first()?->id;
            if (!$experienceTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience listing type not found'
                ], 404);
            }

            $listing = Listing::where('id', $id)
                ->where('user_id', $user->id)
                ->where('listing_type_id', $experienceTypeId)
                ->with(['experience', 'listingType'])
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Experience fetched successfully',
                'data' => $listing
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch experience: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified experience.
     */
    public function update(UpdateExperienceRequest $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user is a host
            if (!$user->is_host) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage experiences'
                ], 403);
            }

            // Get experience listing type ID
            $experienceTypeId = \App\Models\ListingType::where('name', 'experience')->first()?->id;
            if (!$experienceTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience listing type not found'
                ], 404);
            }

            $listing = Listing::where('id', $id)
                ->where('user_id', $user->id)
                ->where('listing_type_id', $experienceTypeId)
                ->with('experience')
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience not found'
                ], 404);
            }

            DB::beginTransaction();

            // Update the main listing
            $listing->update([
                'title' => $request->title ?? $listing->title,
                'description' => $request->description ?? $listing->description,
                'price' => $request->price ?? $listing->price,
                'currency' => $request->currency ?? $listing->currency,
                'address' => $request->address ?? $listing->address,
                'location' => $request->city ?? $listing->location,
                'county' => $request->state ?? $listing->county,
                'country' => $request->country ?? $listing->country,
                'latitude' => $request->latitude ?? $listing->latitude,
                'longitude' => $request->longitude ?? $listing->longitude,
                'status' => $request->status ?? $listing->status,
                'images' => $request->has('images') ? json_encode($request->images) : $listing->images,
                'amenities' => $request->has('amenities') ? json_encode($request->amenities) : $listing->amenities
            ]);

            // Update the experience-specific data
            if ($listing->experience) {
                $listing->experience->update([
                    'duration' => $request->duration ?? $listing->experience->duration,
                    'activity_type' => $request->activity_type ?? $listing->experience->activity_type,
                    'group_size' => $request->group_size ?? $listing->experience->group_size,
                    'difficulty_level' => $request->difficulty_level ?? $listing->experience->difficulty_level,
                    'included_items' => $request->has('included_items') ? json_encode($request->included_items) : $listing->experience->included_items,
                    'requirements' => $request->requirements ?? $listing->experience->requirements,
                    'cancellation_policy' => $request->cancellation_policy ?? $listing->experience->cancellation_policy
                ]);
            }

            DB::commit();

            // Reload the updated experience
            $listing->load('experience');

            return response()->json([
                'success' => true,
                'message' => 'Experience updated successfully',
                'data' => $listing
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update experience: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified experience.
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user is a host
            if (!$user->is_host) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage experiences'
                ], 403);
            }

            // Get experience listing type ID
            $experienceTypeId = \App\Models\ListingType::where('name', 'experience')->first()?->id;
            if (!$experienceTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience listing type not found'
                ], 404);
            }

            $listing = Listing::where('id', $id)
                ->where('user_id', $user->id)
                ->where('listing_type_id', $experienceTypeId)
                ->with('experience')
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Experience not found'
                ], 404);
            }

            DB::beginTransaction();

            // Delete the experience-specific data first
            if ($listing->experience) {
                $listing->experience->delete();
            }

            // Delete the main listing
            $listing->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Experience deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete experience: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete experience',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}