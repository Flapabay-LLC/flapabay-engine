<?php

namespace App\Http\Controllers;

use App\Models\CoHost;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoHostController extends Controller
{
    /**
     * Add a property to the co-host whitelist
     */
    public function addPropertyToWhitelist(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'listing_id' => 'required|exists:properties,id',
                'co_user_id' => 'required|exists:users,id',
                // 'permissions' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if the current user is the owner of the property
            $property = Property::findOrFail($request->listing_id);
            if ($property->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to add co-hosts to this property'
                ], 403);
            }

            // Create co-host record
            $coHost = CoHost::create([
                'user_id' => auth()->user()->id,
                'co_user_id' => $request->co_user_id,
                'listing_id' => $request->listing_id,
                // 'permissions' => $request->permissions,
                'status' => 'pending',
                'joined_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Property added to co-host whitelist successfully',
                'data' => $coHost
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add property to whitelist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sign up as a co-host
     */
    public function signUpAsCoHost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'listing_id' => 'required|exists:properties,id',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if there's a pending invitation
            $coHost = CoHost::where([
                'user_id' => $request->user_id,
                'co_user_id' => auth()->user()->id,
                'listing_id' => $request->listing_id,
                'status' => 'pending'
            ])->first();

            if (!$coHost) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No pending invitation found'
                ], 404);
            }

            // Update status to active
            $coHost->update([
                'status' => 'active',
                'joined_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully signed up as co-host',
                'data' => $coHost
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sign up as co-host',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get properties managed by a co-host
     */
    public function getPropertiesManagedByCoHost(Request $request)
    {
        try {
            $coHostId = $request->co_user_id ?? auth()->user()->id;

            $properties = Property::whereHas('coHosts', function ($query) use ($coHostId) {
                $query->where('co_user_id', $coHostId)
                    ->where('status', 'active');
            })->with(['coHosts' => function ($query) use ($coHostId) {
                $query->where('co_user_id', $coHostId);
            }])->get();

            return response()->json([
                'status' => 'success',
                'data' => $properties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch managed properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get host's co-host members
     */
    public function getHostCoHostMembers(Request $request)
    {
        try {
            $hostId = $request->user_id ?? auth()->user()->id;

            $coHosts = CoHost::where('user_id', $hostId)
                ->with(['coHost', 'property'])
                ->get()
                ->groupBy('listing_id');

            return response()->json([
                'status' => 'success',
                'data' => $coHosts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch co-host members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}