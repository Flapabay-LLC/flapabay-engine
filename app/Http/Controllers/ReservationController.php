<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\listing;
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
    public function store(Request $request)
    {
        // dd('here');
        try {
            $validator = Validator::make($request->all(), [
                'listing_id' => 'required|exists:listings,id',
                'check_in_date' => 'required|date|after:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'number_of_guests' => 'required|integer|min:1',
                'number_of_children' => 'nullable|integer|min:0',
                'number_of_infants' => 'nullable|integer|min:0',
                'number_of_pets' => 'nullable|integer|min:0',
                'special_requests' => 'nullable|string|max:1000',
                'is_instant_booking' => 'boolean',
                'guest_phone' => 'nullable|string',
                'guest_email' => 'nullable|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the listing
            $listing = listing::findOrFail($request->listing_id);

            // Check if the listing is available for the selected dates
            $isAvailable = $this->checklistingAvailability($listing, $request->check_in_date, $request->check_out_date);

            
            if (!$isAvailable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This listing is not available for the selected dates'
                ], 400);
            }

            // Validate guest capacity
            $totalGuests = $request->number_of_guests + ($request->number_of_children ?? 0) + ($request->number_of_infants ?? 0);
            $maxGuests = $listing->maximum_guests ?? $listing->num_of_guests ?? 10; // Fallback to num_of_guests or default 10
            if ($totalGuests > $maxGuests) {
                return response()->json([
                    'status' => 'error',
                    'message' => "This listing can accommodate a maximum of {$maxGuests} guests"
                ], 400);
            }

            // Calculate total price
            $priceBreakdown = $this->calculatePriceBreakdown($listing, $request->check_in_date, $request->check_out_date, $request->number_of_guests, $request->number_of_children ?? 0, $request->number_of_infants ?? 0, $request->number_of_pets ?? 0);

            DB::beginTransaction();

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'listing_id' => $request->listing_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'number_of_guests' => $request->number_of_guests,
                'number_of_children' => $request->number_of_children ?? 0,
                'number_of_infants' => $request->number_of_infants ?? 0,
                'number_of_pets' => $request->number_of_pets ?? 0,
                'total_price' => $priceBreakdown['total'],
                'currency' => $listing->currency,
                'status' => $request->is_instant_booking ? 'confirmed' : 'pending',
                'special_requests' => $request->special_requests,
                'is_instant_booking' => $request->is_instant_booking ?? false,
                'guest_phone' => $request->guest_phone,
                'guest_email' => $request->guest_email,
                'price_breakdown' => $priceBreakdown, // Save breakdown
                'expires_at' => now()->addDays(5), // Set expiration
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation created successfully',
                'data' => [
                    'reservation' => $reservation->load(['listing', 'user']),
                    'price_breakdown' => $priceBreakdown
                ]
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
            $query = Reservation::with(['listing', 'user'])
                ->where('user_id', Auth::id());

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by listing
            if ($request->has('listing_id')) {
                $query->where('listing_id', $request->listing_id);
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
            $reservation = Reservation::with(['listing', 'user'])
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
    private function checklistingAvailability($listing, $checkIn, $checkOut)
    {
        // First check if the listing has its own availability dates set
        if ($listing->check_in_date && $listing->check_out_date) {
            $listingCheckIn = Carbon::parse($listing->check_in_date);
            $listingCheckOut = Carbon::parse($listing->check_out_date);
            $requestCheckIn = Carbon::parse($checkIn);
            $requestCheckOut = Carbon::parse($checkOut);

            // Check if the requested dates fall within the listing's availability
            if ($requestCheckIn < $listingCheckIn || $requestCheckOut > $listingCheckOut) {
                return false;
            }
        }

        // Check for existing reservations that conflict with the requested dates
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
     * Calculate the price breakdown for a reservation (Airbnb-style)
     */
    private function calculatePriceBreakdown($listing, $checkIn, $checkOut, $guests, $children = 0, $infants = 0, $pets = 0)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        // Base price for the stay
        $basePrice = $listing->price_per_night * $nights;
        
        // Additional guest charges
        $additionalGuestPrice = 0;
        $baseGuestCount = $listing->num_of_guests ?? 1;
        if ($guests > $baseGuestCount && $listing->additional_guest_price) {
            $additionalGuests = $guests - $baseGuestCount;
            $additionalGuestPrice = $listing->additional_guest_price * $additionalGuests * $nights;
        }

        // Children charges
        $childrenPrice = 0;
        if ($children > 0 && $listing->children_price) {
            $childrenPrice = $listing->children_price * $children * $nights;
        }

        // Pet charges (if listing allows pets)
        $petPrice = 0;
        if ($pets > 0 && $listing->pet_guests) {
            // Assuming a standard pet fee per night
            $petPrice = 10 * $pets * $nights; // $10 per pet per night
        }

        // Service fee (Airbnb-style)
        $serviceFee = ($basePrice + $additionalGuestPrice + $childrenPrice + $petPrice) * 0.12; // 12% service fee

        // Total calculation
        $subtotal = $basePrice + $additionalGuestPrice + $childrenPrice + $petPrice;
        $total = $subtotal + $serviceFee;

        return [
            'nights' => $nights,
            'base_price' => round($basePrice, 2),
            'additional_guest_price' => round($additionalGuestPrice, 2),
            'children_price' => round($childrenPrice, 2),
            'pet_price' => round($petPrice, 2),
            'subtotal' => round($subtotal, 2),
            'service_fee' => round($serviceFee, 2),
            'total' => round($total, 2),
            'currency' => $listing->currency
        ];
    }

    /**
     * Get listing availability for a date range
     */
    public function getlistingAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'listing_id' => 'required|exists:listings,id',
                'check_in_date' => 'required|date|after:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'number_of_guests' => 'required|integer|min:1',
                'number_of_children' => 'nullable|integer|min:0',
                'number_of_infants' => 'nullable|integer|min:0',
                'number_of_pets' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $listing = listing::findOrFail($request->listing_id);

            // Check availability
            $isAvailable = $this->checklistingAvailability($listing, $request->check_in_date, $request->check_out_date);

            // Calculate price breakdown
            $priceBreakdown = $this->calculatePriceBreakdown(
                $listing,
                $request->check_in_date,
                $request->check_out_date,
                $request->number_of_guests,
                $request->number_of_children ?? 0,
                $request->number_of_infants ?? 0,
                $request->number_of_pets ?? 0
            );

            // Validate guest capacity
            $totalGuests = $request->number_of_guests + ($request->number_of_children ?? 0) + ($request->number_of_infants ?? 0);
            $maxGuests = $listing->maximum_guests ?? $listing->num_of_guests ?? 10; // Fallback to num_of_guests or default 10
            $capacityExceeded = $totalGuests > $maxGuests;

            return response()->json([
                'status' => 'success',
                'message' => 'listing availability checked successfully',
                'data' => [
                    'listing' => [
                        'id' => $listing->id,
                        'title' => $listing->title,
                        'maximum_guests' => $maxGuests,
                        'currency' => $listing->currency
                    ],
                    'availability' => [
                        'is_available' => $isAvailable && !$capacityExceeded,
                        'capacity_exceeded' => $capacityExceeded,
                        'max_guests_allowed' => $maxGuests,
                        'requested_guests' => $totalGuests
                    ],
                    'price_breakdown' => $priceBreakdown
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check listing availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reservations for listings/listings owned by the authenticated host
     */
    public function hostReservations(Request $request)
    {
        try {
            $user = $request->user();
            // Only allow hosts
            if (!$user->isHost()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not a host.'
                ], 403);
            }
            // Get all reservations where the listing belongs to a listing with this user_id or listing.user_id
            $query = Reservation::with(['listing', 'user'])
                ->whereHas('listing', function ($q) use ($user) {
                    $q->whereHas('listing', function ($lq) use ($user) {
                        $lq->where('user_id', $user->id);
                    })
                    ->orWhere('user_id', $user->id);
                });

            // Optional filters (status, date, etc.)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('start_date')) {
                $query->where('check_in_date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('check_out_date', '<=', $request->end_date);
            }
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            $perPage = $request->input('per_page', 10);
            $reservations = $query->paginate($perPage);
            return response()->json([
                'status' => 'success',
                'message' => 'Host reservations fetched successfully',
                'data' => $reservations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch host reservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}