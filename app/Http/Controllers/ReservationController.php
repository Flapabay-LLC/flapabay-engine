<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Property;
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
                'property_id' => 'required|exists:properties,id',
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

            // Get the property
            $property = Property::findOrFail($request->property_id);

            // Check if the property is available for the selected dates
            $isAvailable = $this->checkPropertyAvailability($property, $request->check_in_date, $request->check_out_date);

            
            if (!$isAvailable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This property is not available for the selected dates'
                ], 400);
            }

            // Validate guest capacity
            $totalGuests = $request->number_of_guests + ($request->number_of_children ?? 0) + ($request->number_of_infants ?? 0);
            $maxGuests = $property->maximum_guests ?? $property->num_of_guests ?? 10; // Fallback to num_of_guests or default 10
            if ($totalGuests > $maxGuests) {
                return response()->json([
                    'status' => 'error',
                    'message' => "This property can accommodate a maximum of {$maxGuests} guests"
                ], 400);
            }

            // Calculate total price
            $priceBreakdown = $this->calculatePriceBreakdown($property, $request->check_in_date, $request->check_out_date, $request->number_of_guests, $request->number_of_children ?? 0, $request->number_of_infants ?? 0, $request->number_of_pets ?? 0);

            DB::beginTransaction();

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'property_id' => $request->property_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'number_of_guests' => $request->number_of_guests,
                'number_of_children' => $request->number_of_children ?? 0,
                'number_of_infants' => $request->number_of_infants ?? 0,
                'number_of_pets' => $request->number_of_pets ?? 0,
                'total_price' => $priceBreakdown['total'],
                'currency' => $property->currency,
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
                    'reservation' => $reservation->load(['property', 'user']),
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
            $query = Reservation::with(['property', 'user'])
                ->where('user_id', Auth::id());

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by property
            if ($request->has('property_id')) {
                $query->where('property_id', $request->property_id);
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
            $reservation = Reservation::with(['property', 'user'])
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
     * Check if a property is available for the selected dates
     */
    private function checkPropertyAvailability($property, $checkIn, $checkOut)
    {
        // First check if the property has its own availability dates set
        if ($property->check_in_date && $property->check_out_date) {
            $propertyCheckIn = Carbon::parse($property->check_in_date);
            $propertyCheckOut = Carbon::parse($property->check_out_date);
            $requestCheckIn = Carbon::parse($checkIn);
            $requestCheckOut = Carbon::parse($checkOut);

            // Check if the requested dates fall within the property's availability
            if ($requestCheckIn < $propertyCheckIn || $requestCheckOut > $propertyCheckOut) {
                return false;
            }
        }

        // Check for existing reservations that conflict with the requested dates
        $existingReservations = Reservation::where('property_id', $property->id)
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
    private function calculatePriceBreakdown($property, $checkIn, $checkOut, $guests, $children = 0, $infants = 0, $pets = 0)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        // Base price for the stay
        $basePrice = $property->price_per_night * $nights;
        
        // Additional guest charges
        $additionalGuestPrice = 0;
        $baseGuestCount = $property->num_of_guests ?? 1;
        if ($guests > $baseGuestCount && $property->additional_guest_price) {
            $additionalGuests = $guests - $baseGuestCount;
            $additionalGuestPrice = $property->additional_guest_price * $additionalGuests * $nights;
        }

        // Children charges
        $childrenPrice = 0;
        if ($children > 0 && $property->children_price) {
            $childrenPrice = $property->children_price * $children * $nights;
        }

        // Pet charges (if property allows pets)
        $petPrice = 0;
        if ($pets > 0 && $property->pet_guests) {
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
            'currency' => $property->currency
        ];
    }

    /**
     * Get property availability for a date range
     */
    public function getPropertyAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id' => 'required|exists:properties,id',
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

            $property = Property::findOrFail($request->property_id);

            // Check availability
            $isAvailable = $this->checkPropertyAvailability($property, $request->check_in_date, $request->check_out_date);

            // Calculate price breakdown
            $priceBreakdown = $this->calculatePriceBreakdown(
                $property,
                $request->check_in_date,
                $request->check_out_date,
                $request->number_of_guests,
                $request->number_of_children ?? 0,
                $request->number_of_infants ?? 0,
                $request->number_of_pets ?? 0
            );

            // Validate guest capacity
            $totalGuests = $request->number_of_guests + ($request->number_of_children ?? 0) + ($request->number_of_infants ?? 0);
            $maxGuests = $property->maximum_guests ?? $property->num_of_guests ?? 10; // Fallback to num_of_guests or default 10
            $capacityExceeded = $totalGuests > $maxGuests;

            return response()->json([
                'status' => 'success',
                'message' => 'Property availability checked successfully',
                'data' => [
                    'property' => [
                        'id' => $property->id,
                        'title' => $property->title,
                        'maximum_guests' => $maxGuests,
                        'currency' => $property->currency
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
                'message' => 'Failed to check property availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 