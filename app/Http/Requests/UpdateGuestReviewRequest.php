<?php

namespace App\Http\Requests;

use App\Models\UserReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateGuestReviewRequest extends FormRequest
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

        // Check if review exists and belongs to user
        $review = UserReview::where('id', $this->route('reviewId'))
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            return false;
        }

        // Allow updating both draft and published reviews
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

        $review = UserReview::where('id', $this->route('reviewId'))
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            abort(404, 'Review not found.');
        }

        abort(403, 'Unauthorized to update this review.');
    }
}
