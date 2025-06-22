<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\ListingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;




class DashboardController extends Controller
{
    /**
     * Return the dashboard view.
     */
    public function index()
    {
        return view('dashboard'); // Loads dashboard.blade.php
    }

    /**
     * Return raw dashboard data for frontend use.
     */
    public function fetchDashboardData()
    {
        try {
            $listings = Listing::latest()->take(5)->get();
            $payments = Payment::latest()->take(5)->get();
            $bookings = Booking::latest()->take(5)->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data fetched successfully',
                'data' => [
                    'listings' => $listings,
                    'payments' => $payments,
                    'bookings' => $bookings,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
