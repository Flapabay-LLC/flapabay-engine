<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Booking;
use App\Models\Option;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PayoutOption;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id',  // Ensure the booking exists
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',  // Allowed payment methods
            'amount' => 'required|numeric|min:0.01',  // Ensure a valid positive amount
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Fetch the booking record
        $booking = Booking::where('id',$request->input('booking_id'))->first();

        // Check if booking is valid and not already paid
        if ($booking->payment_status == 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'This booking has already been paid'
            ], 400);
        }

        // Check if the amount matches the booking total
        if ($booking->amount != $request->input('amount')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount does not match the booking total'
            ], 400);
        }

        // Process the payment (this is a placeholder for actual payment processing logic)
        // You would typically call an external API here (e.g., for Stripe, PayPal, etc.)
        $paymentStatus = $this->processPayment($request->input('payment_method'), $request->input('amount'));

        if ($paymentStatus['status'] == 'success') {
            // Update booking with payment details
            $booking->payment_status = 'paid';
            $booking->payment_method = $request->input('payment_method');
            $booking->payment_date = Carbon::now();
            $booking->save();

            Payment::create([
                'booking_id'=>$booking->id,
                'payment_method'=>$request->input('payment_method'),
                'amount'=>$request->input('amount'),
                'status'=>'paid',
            ]);
            // Respond with success
            return response()->json([
                'status' => 'success',
                'message' => 'Payment successful',
                'data' => $booking
            ], 200);
        }

        // If payment fails, return error
        return response()->json([
            'status' => 'error',
            'message' => 'Payment failed, please try again'
        ], 500);
    }

    /**
     * Simulate payment processing. This should be replaced with actual payment gateway integration.
     */
    private function processPayment($paymentMethod, $amount)
    {
        // Simulate payment gateway logic (replace with actual API call)
        // For example, use Stripe, PayPal, etc.
        if ($paymentMethod == 'credit_card' || $paymentMethod == 'paypal') {
            return [
                'status' => 'success',  // Simulated success
                'transaction_id' => uniqid('txn_')  // Generate a dummy transaction ID
            ];
        }
        return [
            'status' => 'error'
        ];
    }

    public function status(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|integer|exists:payments,id',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $details = Payment::where('id', $request->input('payment_id'))->first();

        // If payment fails, return error
        return response()->json([
            'status' => 'error',
            'message' => 'Payment failed, please try again',
            'data' => $details
        ], 500);
    }

    public function options()
    {
        $options = PayoutOption::get();
        return response()->json([
            'status' => 'error',
            'message' => 'Availability system payment options',
            'data' => $options
        ], 200);
    }

    public function addOption(Request $request)
    {
        try {
                    // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required', // Ensure user exists
            'description' => 'required', // Allowed payment methods
            'icon' => 'required',
            'icon_alt' => 'required', // MM/YY format for expiration date
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Create the payment option
        $paymentOption = PayoutOption::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'icon' => $request->input('icon'),
            'icon_alt' => $request->input('icon_alt'),
            'currency' => $request->input('currency', 'USD'),
        ]);

        // Return success response with the created payment option data
        return response()->json([
            'status' => 'success',
            'message' => $request->input('name').' payment option added successfully',
            'data' => $paymentOption
        ], 201);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function editOption(Request $request)
    {

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required', // Ensure user exists
            'description' => 'required', // Allowed payment methods
            'icon' => 'required',
            'icon_alt' => 'required', // MM/YY format for expiration date
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Find the payment option by ID
        $paymentOption = PayoutOption::find($request->input('option_id'));
        if (!$paymentOption) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment option not found.'
            ], 404);
        }

        // Update the payment option with the new values
        $paymentOption->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'icon' => $request->input('icon'),
            'icon_alt' => $request->input('icon_alt'),
            'currency' => $request->input('currency', 'USD'),
        ]);

        // Return success response with the updated payment option data
        return response()->json([
            'status' => 'success',
            'message' => 'Payment option updated successfully.',
            'data' => $paymentOption
        ], 200);
    }


    public function getUserPaymentDetails($user_id)
    {
        $options = PaymentMethod::where('user_id', $user_id)->get();
        return response()->json([
            'status' => 'error',
            'message' => 'Availability system payment options',
            'data' => $options
        ], 200);
    }


    public function addUserPaymentDetails(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id', // Ensure user exists
            'payment_method' => 'required|string|in:credit_card,bank_transfer,paypal,mobile', // Allowed payment methods
            'account_number' => 'required|string',
            'expiration_date' => 'required', // MM/YY format for expiration date
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Create the payment option
        $paymentOption = PaymentMethod::create([
            'user_id' => $request->input('user_id'),
            'payment_method' => $request->input('payment_method'),
            'account_number' => $request->input('account_number'),
            'expiration_date' => $request->input('expiration_date'),
            'country_code' => $request->input('country_code', 'US'), // Default to 'US' if not provided
            'currency' => $request->input('currency', 'USD'), // Default to 'USD' if not provided
        ]);

        // Return success response with the created payment option data
        return response()->json([
            'status' => 'success',
            'message' => 'Payment option added successfully',
            'data' => $paymentOption
        ], 201);
    }

    public function editUserPaymentDetails(Request $request, $id)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id', // Ensure user exists
            'payment_method' => 'required|string|in:credit_card,bank_transfer,paypal,mobile', // Allowed payment methods
            'account_number' => 'required|string',
            'expiration_date' => 'required',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Find the payment option by ID
        $paymentOption = PaymentMethod::find($id);

        // If the payment option does not exist, return a 404 response
        if (!$paymentOption) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment option not found.'
            ], 404);
        }

        // Update the payment option with the new values
        $paymentOption->update([
            'user_id' => $request->input('user_id'),
            'payment_method' => $request->input('payment_method'),
            'account_number' => $request->input('account_number'),
            'expiration_date' => $request->input('expiration_date'),
            'country_code' => $request->input('country_code', 'US'), // Default to 'US' if not provided
            'currency' => $request->input('currency', 'USD'), // Default to 'USD' if not provided
        ]);

        // Return success response with the updated payment option data
        return response()->json([
            'status' => 'success',
            'message' => 'Payment option updated successfully.',
            'data' => $paymentOption
        ], 200);
    }
}
