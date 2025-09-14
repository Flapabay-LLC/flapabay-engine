<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EarningsController extends Controller
{
    /**
     * Get user's current balance
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalance(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can access balance information',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $user->balance,
                    'currency' => $user->currency ?? 'USD',
                    'last_updated' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's pending earnings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPendingEarnings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can access earnings information',
                ], 403);
            }

            // Get pending earnings from recent bookings (within last 30 days)
            $pendingBookings = $user->hostBookings()
                ->where('payment_status', 'completed')
                ->where('booking_status', 'completed')
                ->where('bookings.created_at', '>=', Carbon::now()->subDays(30))
                ->with(['listing', 'payment'])
                ->get();

            $pendingAmount = $pendingBookings->sum('amount') * 0.85; // Assuming 15% platform fee

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_earnings' => $user->pending_earnings,
                    'calculated_pending' => number_format($pendingAmount, 2),
                    'currency' => $user->currency ?? 'USD',
                    'pending_bookings_count' => $pendingBookings->count(),
                    'release_date' => Carbon::now()->addDays(7)->format('Y-m-d'), // Assuming 7-day hold
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending earnings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's total earnings summary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotalEarnings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can access earnings information',
                ], 403);
            }

            // Calculate total earnings from all completed bookings
            $totalBookingsAmount = $user->hostBookings()
                ->where('payment_status', 'completed')
                ->where('booking_status', 'completed')
                ->sum('amount');

            $platformFee = $totalBookingsAmount * 0.15; // 15% platform fee
            $netEarnings = $totalBookingsAmount - $platformFee;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => $user->total_earnings,
                    'calculated_total' => number_format($netEarnings, 2),
                    'gross_bookings' => number_format($totalBookingsAmount, 2),
                    'platform_fees' => number_format($platformFee, 2),
                    'total_withdrawn' => $user->total_withdrawn,
                    'available_balance' => $user->balance,
                    'currency' => $user->currency ?? 'USD',
                    'last_payout' => $user->last_payout_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve total earnings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly earnings breakdown
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMonthlyEarnings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can access earnings information',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
                'months' => 'nullable|integer|min:1|max:24'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $year = $request->get('year', date('Y'));
            $monthsBack = $request->get('months', 12);

            // Get monthly breakdown
            $monthlyData = $user->hostBookings()
                ->select(
                    DB::raw('YEAR(bookings.created_at) as year'),
                    DB::raw('MONTH(bookings.created_at) as month'),
                    DB::raw('COUNT(*) as bookings_count'),
                    DB::raw('SUM(bookings.amount) as gross_earnings'),
                    DB::raw('SUM(bookings.amount * 0.85) as net_earnings')
                )
                ->where('bookings.payment_status', 'completed')
                ->where('bookings.booking_status', 'completed')
                ->where('bookings.created_at', '>=', Carbon::now()->subMonths($monthsBack))
                ->groupBy('year', 'month', 'listings.user_id')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'month' => $item->month,
                        'month_name' => Carbon::create($item->year, $item->month)->format('F'),
                        'bookings_count' => $item->bookings_count,
                        'gross_earnings' => number_format($item->gross_earnings, 2),
                        'net_earnings' => number_format($item->net_earnings, 2),
                        'platform_fees' => number_format($item->gross_earnings * 0.15, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_breakdown' => $monthlyData,
                    'currency' => $user->currency ?? 'USD',
                    'period' => [
                        'months_back' => $monthsBack,
                        'from' => Carbon::now()->subMonths($monthsBack)->format('Y-m-d'),
                        'to' => Carbon::now()->format('Y-m-d')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve monthly breakdown',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}