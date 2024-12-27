<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{

    public function getBookings()
    {
        try {
            $bookings = Booking::get();
            return response()->json([
                'success' => true,
                'message' => 'All Bookings',
                'booking' => $bookings,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBooking($book_id)
    {
        try {
            $booking = Booking::where('id', $book_id)->first();
            return response()->json([
                'success' => true,
                'message' => 'Booking Information',
                'booking' => $booking,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function createBooking(Request $request)
    {
        // Step 1: Validate incoming request data
        $validatedData = Validator::make($request->all(), [
            'property_id' => 'required', // Ensure property exists
            'user_id' => 'required', // Ensure user exists
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'guest_details' => 'nullable|string',
            'guest_count' => 'required|integer|min:1',
            'amount' => 'required|integer|min:1',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validatedData->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 2: Create the booking
            $booking = Booking::create([
                'booking_number' => uniqid('booking_'), // Generate a unique booking number
                'property_id' => $request->input('property_id'),
                'amount' => $request->input('amount'),
                'user_id' => $request->input('user_id'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'guest_details' => $request->input('guest_details'),
                'guest_count' => $request->input('guest_count'),
                'booking_status' => 'pending', // Default status
                'payment_status' => 'pending', // Default status
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelbooking(Request $request, $book_id)
    {
        // Find the booking by ID
        $booking = Booking::find($book_id);

        // Check if the booking exists
        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        // Update the booking cancellation details
        $booking->cancellation_reason = $request->input('reason', 'No reason provided');
        $booking->cancellation_date = Carbon::now();
        $booking->booking_status = 'cancelled';
        $booking->save();

        // Fetch the user details to send email
        $user = User::find($booking->user_id);

        if ($user) {
            // Send cancellation email
            Mail::raw(
                "Your booking has been cancelled. Reason: {$booking->cancellation_reason}",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Booking Cancelled');
                }
            );
        }

        // Return the updated booking as a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }


}
