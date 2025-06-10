<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Create a new reservation
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'listing_id' => 'required|exists:listings,id',
                'check_in_date' => 'required|date|after:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'number_of_guests' => 'required|integer|min:1',
                'number_of_children' => 'nullable|integer|min:0',
                'special_requests' => 'nullable|string',
                'is_instant_booking' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the listing
            $listing = Listing::with('property')->findOrFail($request->listing_id);

            // Check if the listing is available for the selected dates
            $isAvailable = $this->checkAvailability($listing, $request->check_in_date, $request->check_out_date);
            if (!$isAvailable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The listing is not available for the selected dates'
                ], 400);
            }

            // Calculate total price
            $totalPrice = $this->calculateTotalPrice($listing, $request->check_in_date, $request->check_out_date, $request->number_of_guests, $request->number_of_children);

            DB::beginTransaction();

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'listing_id' => $request->listing_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'number_of_guests' => $request->number_of_guests,
                'number_of_children' => $request->number_of_children,
                'total_price' => $totalPrice,
                'currency' => $listing->property->currency,
                'status' => $request->is_instant_booking ? 'confirmed' : 'pending',
                'special_requests' => $request->special_requests,
                'is_instant_booking' => $request->is_instant_booking
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation created successfully',
                'data' => $reservation->load(['listing', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reservations for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $query = Reservation::with(['listing', 'listing.property'])
                ->where('user_id', Auth::id());

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('check_in_date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('check_out_date', '<=', $request->end_date);
            }

            // Sort by
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->input('per_page', 10);
            $reservations = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Reservations fetched successfully',
                'data' => $reservations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific reservation
     */
    public function show($id)
    {
        try {
            $reservation = Reservation::with(['listing', 'listing.property', 'user'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation fetched successfully',
                'data' => $reservation
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     */
    public function cancel(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reservation = Reservation::where('user_id', Auth::id())
                ->findOrFail($id);

            if ($reservation->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reservation is already cancelled'
                ], 400);
            }

            if ($reservation->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot cancel a completed reservation'
                ], 400);
            }

            DB::beginTransaction();

            $reservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation cancelled successfully',
                'data' => $reservation
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a listing is available for the selected dates
     */
    private function checkAvailability($listing, $checkIn, $checkOut)
    {
        $existingReservations = Reservation::where('listing_id', $listing->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                            ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->exists();

        return !$existingReservations;
    }

    /**
     * Calculate the total price for a reservation
     */
    private function calculateTotalPrice($listing, $checkIn, $checkOut, $guests, $children = 0)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        $basePrice = $listing->property->price_per_night * $nights;
        $additionalGuestPrice = 0;
        $childrenPrice = 0;

        // Calculate additional guest price if applicable
        if ($guests > $listing->property->num_of_guests && $listing->property->additional_guest_price) {
            $additionalGuests = $guests - $listing->property->num_of_guests;
            $additionalGuestPrice = $listing->property->additional_guest_price * $additionalGuests * $nights;
        }

        // Calculate children price if applicable
        if ($children > 0 && $listing->property->children_price) {
            $childrenPrice = $listing->property->children_price * $children * $nights;
        }

        return $basePrice + $additionalGuestPrice + $childrenPrice;
    }
} 