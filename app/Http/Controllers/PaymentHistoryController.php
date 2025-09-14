<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentHistoryController extends Controller
{
    /**
     * Get comprehensive payment history for user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaymentHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|string|in:all,earnings,payments,withdrawals',
                'status' => 'nullable|string|in:pending,completed,failed,cancelled',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type', 'all');
            $status = $request->status;
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : null;
            $perPage = $request->get('per_page', 20);

            $transactions = collect();

            // Get earnings (for hosts)
            if (($type === 'all' || $type === 'earnings') && $user->isHost()) {
                $earnings = $this->getEarningsHistory($user, $status, $dateFrom, $dateTo);
                $transactions = $transactions->merge($earnings);
            }

            // Get payments made (for guests)
            if ($type === 'all' || $type === 'payments') {
                $payments = $this->getPaymentsHistory($user, $status, $dateFrom, $dateTo);
                $transactions = $transactions->merge($payments);
            }

            // Get withdrawals (for hosts)
            if (($type === 'all' || $type === 'withdrawals') && $user->isHost()) {
                $withdrawals = $this->getWithdrawalsHistory($user, $status, $dateFrom, $dateTo);
                $transactions = $transactions->merge($withdrawals);
            }

            // Sort by date descending
            $transactions = $transactions->sortByDesc('date');

            // Paginate manually
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedTransactions = $transactions->slice($offset, $perPage)->values();
            $total = $transactions->count();
            $lastPage = ceil($total / $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $paginatedTransactions,
                    'summary' => $this->getTransactionSummary($transactions, $user),
                    'pagination' => [
                        'current_page' => $currentPage,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => $lastPage
                    ],
                    'filters_applied' => [
                        'type' => $type,
                        'status' => $status,
                        'date_from' => $dateFrom?->format('Y-m-d'),
                        'date_to' => $dateTo?->format('Y-m-d')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earnings history for host
     *
     * @param User $user
     * @param string|null $status
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getEarningsHistory(User $user, ?string $status, ?Carbon $dateFrom, ?Carbon $dateTo)
    {
        $query = $user->hostBookings()
            ->with(['listing', 'user', 'payment'])
            ->where('payment_status', 'completed')
            ->where('booking_status', 'completed');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo->endOfDay());
        }

        $bookings = $query->get();

        return $bookings->map(function ($booking) {
            $grossAmount = $booking->amount;
            $platformFee = $grossAmount * 0.15; // 15% platform fee
            $netAmount = $grossAmount - $platformFee;

            return [
                'id' => 'earning_' . $booking->id,
                'type' => 'earning',
                'description' => 'Booking payment from ' . $booking->user->fname . ' ' . $booking->user->lname,
                'amount' => $netAmount,
                'gross_amount' => $grossAmount,
                'platform_fee' => $platformFee,
                'currency' => $booking->listing->currency ?? 'USD',
                'status' => 'completed',
                'date' => $booking->created_at,
                'reference_id' => 'BK-' . str_pad($booking->id, 8, '0', STR_PAD_LEFT),
                'details' => [
                    'listing_title' => $booking->listing->title,
                    'guest_name' => $booking->user->fname . ' ' . $booking->user->lname,
                    'booking_dates' => $booking->start_date . ' to ' . $booking->end_date,
                    'guest_count' => $booking->guest_count
                ]
            ];
        });
    }

    /**
     * Get payments history for user
     *
     * @param User $user
     * @param string|null $status
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getPaymentsHistory(User $user, ?string $status, ?Carbon $dateFrom, ?Carbon $dateTo)
    {
        $query = $user->bookings()
            ->with(['listing', 'payment'])
            ->whereHas('payment');

        if ($status) {
            $query->where('payment_status', $status);
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo->endOfDay());
        }

        $bookings = $query->get();

        return $bookings->map(function ($booking) {
            return [
                'id' => 'payment_' . $booking->id,
                'type' => 'payment',
                'description' => 'Payment for ' . $booking->listing->title,
                'amount' => -$booking->amount, // Negative for outgoing payment
                'currency' => $booking->listing->currency ?? 'USD',
                'status' => $booking->payment_status,
                'date' => $booking->payment_date ?? $booking->created_at,
                'reference_id' => 'PY-' . str_pad($booking->payment->id ?? $booking->id, 8, '0', STR_PAD_LEFT),
                'details' => [
                    'listing_title' => $booking->listing->title,
                    'host_name' => $booking->listing->user->fname . ' ' . $booking->listing->user->lname,
                    'booking_dates' => $booking->start_date . ' to ' . $booking->end_date,
                    'payment_method' => $booking->payment_method,
                    'guest_count' => $booking->guest_count
                ]
            ];
        });
    }

    /**
     * Get withdrawals history for user
     *
     * @param User $user
     * @param string|null $status
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getWithdrawalsHistory(User $user, ?string $status, ?Carbon $dateFrom, ?Carbon $dateTo)
    {
        $query = $user->withdrawals()->with('paymentMethod');

        if ($status) {
            $query->where('status', $status);
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo->endOfDay());
        }

        $withdrawals = $query->get();

        return $withdrawals->map(function ($withdrawal) {
            return [
                'id' => 'withdrawal_' . $withdrawal->id,
                'type' => 'withdrawal',
                'description' => 'Withdrawal to ' . $withdrawal->paymentMethod->payment_method,
                'amount' => -$withdrawal->amount, // Negative for outgoing withdrawal
                'currency' => $withdrawal->currency,
                'status' => $withdrawal->status,
                'date' => $withdrawal->requested_at,
                'reference_id' => 'WD-' . str_pad($withdrawal->id, 8, '0', STR_PAD_LEFT),
                'details' => [
                    'payment_method' => $withdrawal->paymentMethod->payment_method,
                    'account_number' => $this->maskAccountNumber($withdrawal->paymentMethod->account_number),
                    'requested_at' => $withdrawal->requested_at,
                    'processed_at' => $withdrawal->processed_at,
                    'completed_at' => $withdrawal->completed_at,
                    'notes' => $withdrawal->notes
                ]
            ];
        });
    }

    /**
     * Get transaction summary
     *
     * @param \Illuminate\Support\Collection $transactions
     * @param User $user
     * @return array
     */
    private function getTransactionSummary($transactions, User $user): array
    {
        $totalEarnings = $transactions->where('type', 'earning')->sum('amount');
        $totalPayments = abs($transactions->where('type', 'payment')->sum('amount'));
        $totalWithdrawals = abs($transactions->where('type', 'withdrawal')->where('status', 'completed')->sum('amount'));
        $pendingWithdrawals = abs($transactions->where('type', 'withdrawal')->where('status', 'pending')->sum('amount'));

        return [
            'total_earnings' => number_format($totalEarnings, 2),
            'total_payments' => number_format($totalPayments, 2),
            'total_withdrawals' => number_format($totalWithdrawals, 2),
            'pending_withdrawals' => number_format($pendingWithdrawals, 2),
            'current_balance' => number_format($user->balance, 2),
            'currency' => $user->currency ?? 'USD',
            'transaction_counts' => [
                'earnings' => $transactions->where('type', 'earning')->count(),
                'payments' => $transactions->where('type', 'payment')->count(),
                'withdrawals' => $transactions->where('type', 'withdrawal')->count()
            ]
        ];
    }

    /**
     * Export payment history to CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPaymentHistory(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|string|in:all,earnings,payments,withdrawals',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'format' => 'nullable|string|in:csv,pdf'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type', 'all');
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : null;
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : null;
            $format = $request->get('format', 'csv');

            $transactions = collect();

            // Get all transactions (same logic as getPaymentHistory but without pagination)
            if (($type === 'all' || $type === 'earnings') && $user->isHost()) {
                $earnings = $this->getEarningsHistory($user, null, $dateFrom, $dateTo);
                $transactions = $transactions->merge($earnings);
            }

            if ($type === 'all' || $type === 'payments') {
                $payments = $this->getPaymentsHistory($user, null, $dateFrom, $dateTo);
                $transactions = $transactions->merge($payments);
            }

            if (($type === 'all' || $type === 'withdrawals') && $user->isHost()) {
                $withdrawals = $this->getWithdrawalsHistory($user, null, $dateFrom, $dateTo);
                $transactions = $transactions->merge($withdrawals);
            }

            $transactions = $transactions->sortByDesc('date');

            if ($format === 'csv') {
                return $this->exportToCsv($transactions, $user);
            }

            // For future PDF export
            return response()->json([
                'success' => false,
                'message' => 'PDF export not yet implemented'
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export transactions to CSV
     *
     * @param \Illuminate\Support\Collection $transactions
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    private function exportToCsv($transactions, User $user)
    {
        $filename = 'payment_history_' . $user->id . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Type',
                'Description',
                'Amount',
                'Currency',
                'Status',
                'Reference ID'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction['date']->format('Y-m-d H:i:s'),
                    ucfirst($transaction['type']),
                    $transaction['description'],
                    $transaction['amount'],
                    $transaction['currency'],
                    ucfirst($transaction['status']),
                    $transaction['reference_id']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mask account number for security
     *
     * @param string $accountNumber
     * @return string
     */
    private function maskAccountNumber(string $accountNumber): string
    {
        if (strlen($accountNumber) <= 4) {
            return str_repeat('*', strlen($accountNumber));
        }
        
        return str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -4);
    }
}