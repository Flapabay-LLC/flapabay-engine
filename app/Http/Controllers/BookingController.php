<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{

    public function index()
    {
        $bookings = Auth::user()->bookings()->with('property')->latest()->paginate(10);
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->first()], 422);
        }

        $property = Property::findOrFail($request->property_id);

        $totalDays = strtotime($request->check_out_date) - strtotime($request->check_in_date);
        $totalDays = floor($totalDays / (60 * 60 * 24));
        $totalPrice = $property->price_per_night * $totalDays;

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'property_id' => $property->id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'amount' => $totalPrice,
            'booking_status' => 'pending',
        ]);

        return response()->json(['message' => 'Booking request sent.', 'booking' => $booking], 201);
    }

    public function show(string $id)
    {
        try {
            $booking = Booking::where('id', $id)->first();
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

    public function update(Request $request, string $id)
    {
        // Find the booking by ID
        $booking = Booking::find($id);

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
        $user = User::where('id', $booking->user_id)->first();

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

    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->delete();
        return response()->json(['message' => 'Booking canceled.']);
    }

    public function generateInvoice(Request $request, $booking_id)
    {
        // Validate booking_id is an integer
        if (!ctype_digit($booking_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid booking ID'
            ], 400);
        }

        // Fetch the booking
        $booking = Booking::find($booking_id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        try {
            // Generate the invoice
            $invoiceData = [
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'payment_id' => null, // Set this if you have a payment system
                'amount' => $booking->amount,
                'status' => 'pending', // Default status
                'payment_method' => 'N/A', // Default payment method, change if applicable
                'due_date' => Carbon::now()->addDays(7), // Example: 7 days from now
                'description' => "Invoice for booking #{$booking->id}",
                'currency' => 'ZMW', // Default currency, adjust as needed
            ];

            $invoice = Invoice::create($invoiceData);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice generated successfully',
                'data' => $invoice
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
