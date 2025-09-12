<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\UserReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreGuestReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Check if booking belongs to user and is completed
        $booking = Booking::where('id', $this->tripId)
            ->where('user_id', $user->id)
            ->where('booking_status', 'completed')
            ->first();

        if (!$booking) {
            return false;
        }

        // Check if review already exists
        $existingReview = UserReview::where('user_id', $user->id)
            ->where('trip_id', $this->tripId)
            ->exists();

        if ($existingReview) {
            return false;
        }

        // Check review deadline (30 days)
        $reviewDeadline = Carbon::parse($booking->end_date)->addDays(30);
        if (Carbon::now()->gt($reviewDeadline)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tripId' => 'required|exists:bookings,id',
            'listingId' => 'required|exists:listings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:2000',
            'status' => 'required|in:draft,published'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'tripId.required' => 'Trip ID is required.',
            'tripId.exists' => 'Invalid trip ID.',
            'listingId.required' => 'Listing ID is required.',
            'listingId.exists' => 'Invalid listing ID.',
            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating cannot be more than 5.',
            'comment.required' => 'Review comment is required.',
            'comment.min' => 'Review comment must be at least 10 characters.',
            'comment.max' => 'Review comment cannot exceed 2000 characters.',
            'status.required' => 'Review status is required.',
            'status.in' => 'Review status must be either draft or published.'
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Authentication required.');
        }

        $booking = Booking::where('id', $this->tripId)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            abort(403, 'Trip not found or does not belong to you.');
        }

        if ($booking->booking_status !== 'completed') {
            abort(403, 'Trip must be completed before reviewing.');
        }

        $existingReview = UserReview::where('user_id', $user->id)
            ->where('trip_id', $this->tripId)
            ->exists();

        if ($existingReview) {
            abort(403, 'Review already exists for this trip.');
        }

        $reviewDeadline = Carbon::parse($booking->end_date)->addDays(30);
        if (Carbon::now()->gt($reviewDeadline)) {
            abort(403, 'Review deadline has passed.');
        }

        abort(403, 'Unauthorized to create this review.');
    }
}
