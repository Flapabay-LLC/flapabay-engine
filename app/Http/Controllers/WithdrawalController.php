<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MySMS;

class WithdrawalController extends Controller
{
    use MySMS;

    /**
     * Request a withdrawal/payout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can request withdrawals',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'payment_method_id' => 'required|exists:payment_methods,id',
                'amount' => 'required|numeric|min:10|max:50000',
                'currency' => 'nullable|string|size:3'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify payment method belongs to user
            $paymentMethod = PaymentMethod::where('id', $request->payment_method_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found or does not belong to you'
                ], 404);
            }

            // Check if user has sufficient balance
            if ($user->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance',
                    'data' => [
                        'requested_amount' => $request->amount,
                        'available_balance' => $user->balance
                    ]
                ], 422);
            }

            // Check for pending withdrawals (limit to 3 pending at a time)
            $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            if ($pendingWithdrawals >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have too many pending withdrawals. Please wait for them to complete.'
                ], 422);
            }

            // Generate OTP for withdrawal verification
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiry = Carbon::now()->addMinutes(10);

            // Update user with OTP
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => $otpExpiry
            ]);

            // Send OTP via email or SMS
            $this->sendWithdrawalOTP($user, $otp, $request->amount);

            // Store withdrawal request temporarily (we'll create it after OTP verification)
            session([
                'withdrawal_request' => [
                    'user_id' => $user->id,
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $request->amount,
                    'currency' => $request->currency ?? $user->currency ?? 'USD',
                    'expires_at' => $otpExpiry
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent for withdrawal verification',
                'data' => [
                    'otp_sent_to' => $user->email ? 'email' : 'phone',
                    'expires_in_minutes' => 10,
                    'amount' => $request->amount,
                    'currency' => $request->currency ?? $user->currency ?? 'USD'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and complete withdrawal request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyWithdrawal(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP'
                ], 422);
            }

            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired'
                ], 422);
            }

            // Get withdrawal request from session
            $withdrawalRequest = session('withdrawal_request');
            if (!$withdrawalRequest || Carbon::now()->gt($withdrawalRequest['expires_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal request has expired. Please start again.'
                ], 422);
            }

            // Verify user still has sufficient balance
            if ($user->balance < $withdrawalRequest['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'payment_method_id' => $withdrawalRequest['payment_method_id'],
                'amount' => $withdrawalRequest['amount'],
                'currency' => $withdrawalRequest['currency'],
                'status' => 'pending',
                'requested_at' => Carbon::now(),
                'metadata' => [
                    'otp_verified_at' => Carbon::now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // Update user balance (deduct the amount)
            $user->decrement('balance', $withdrawalRequest['amount']);
            $user->increment('total_withdrawn', $withdrawalRequest['amount']);

            // Clear OTP and session
            $user->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);
            session()->forget('withdrawal_request');

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency,
                    'status' => $withdrawal->status,
                    'estimated_completion' => Carbon::now()->addBusinessDays(3)->format('Y-m-d'),
                    'reference_id' => 'WD-' . str_pad($withdrawal->id, 8, '0', STR_PAD_LEFT)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's withdrawal history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWithdrawalHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can view withdrawal history',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string|in:pending,processing,completed,failed,cancelled',
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

            $query = $user->withdrawals()->with('paymentMethod');

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 15);
            $withdrawals = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $withdrawals->getCollection()->transform(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency,
                    'status' => $withdrawal->status,
                    'reference_id' => 'WD-' . str_pad($withdrawal->id, 8, '0', STR_PAD_LEFT),
                    'payment_method' => [
                        'type' => $withdrawal->paymentMethod->type,
                        'method' => $withdrawal->paymentMethod->payment_method,
                        'account_number' => $this->maskAccountNumber($withdrawal->paymentMethod->account_number)
                    ],
                    'requested_at' => $withdrawal->requested_at,
                    'processed_at' => $withdrawal->processed_at,
                    'completed_at' => $withdrawal->completed_at,
                    'notes' => $withdrawal->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawals' => $withdrawals->items(),
                    'pagination' => [
                        'current_page' => $withdrawals->currentPage(),
                        'per_page' => $withdrawals->perPage(),
                        'total' => $withdrawals->total(),
                        'last_page' => $withdrawals->lastPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve withdrawal history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending withdrawal
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancelWithdrawal(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $withdrawal = Withdrawal::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            if (!$withdrawal->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal cannot be cancelled in its current status'
                ], 422);
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'cancelled',
                'notes' => 'Cancelled by user'
            ]);

            // Refund the amount to user's balance
            $user->increment('balance', $withdrawal->amount);
            $user->decrement('total_withdrawn', $withdrawal->amount);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal cancelled successfully',
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'refunded_amount' => $withdrawal->amount,
                    'new_balance' => $user->fresh()->balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send OTP for withdrawal verification
     *
     * @param User $user
     * @param string $otp
     * @param float $amount
     * @return void
     */
    private function sendWithdrawalOTP(User $user, string $otp, float $amount): void
    {
        $message = "Your withdrawal verification code is: {$otp}. Amount: {$amount}. Valid for 10 minutes. Do not share this code.";
        
        if ($user->email) {
            // Send via email (you can create a proper email template)
            try {
                Mail::raw($message, function ($mail) use ($user) {
                    $mail->to($user->email)
                         ->subject('Withdrawal Verification Code');
                });
            } catch (\Exception $e) {
                // Fallback to SMS if email fails
                if ($user->phone) {
                    $this->sendSMS($user->phone, $message);
                }
            }
        } elseif ($user->phone) {
            $this->sendSMS($user->phone, $message);
        }
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