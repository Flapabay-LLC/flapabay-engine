<?php

namespace App\Http\Controllers;

use App\Models\UserReview;
use App\Models\Booking;
use App\Models\Listing;
use App\Http\Requests\StoreGuestReviewRequest;
use App\Http\Requests\UpdateGuestReviewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GuestReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.api');
    }

    /**
     * GET /api/reviews/guest
     * Fetch all reviews for authenticated guest
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = min($request->get('limit', 100), 500);
            $offset = $request->get('offset', 0);
            $lastUpdated = $request->get('lastUpdated');

            $query = UserReview::with(['listing:id,title,location', 'listing.images:id,listing_id,image_url'])
                ->where('user_id', $user->id);

            if ($lastUpdated) {
                $query->where('updated_at', '>', $lastUpdated);
            }

            $total = $query->count();
            $reviews = $query->offset($offset)->limit($limit)->get();

            $formattedReviews = $reviews->map(function ($review) {
                $hostResponse = null;
                if ($review->hasHostResponse()) {
                    $hostResponse = [
                        'comment' => $review->host_response_comment,
                        'createdAt' => $review->host_response_created_at ? $review->host_response_created_at->toISOString() : null
                    ];
                }

                return [
                    'id' => (string) $review->id,
                    'listingId' => (string) $review->listing_id,
                    'tripId' => (string) $review->trip_id,
                    'rating' => $review->rating,
                    'comment' => $review->review,
                    'status' => $review->status,
                    'createdAt' => $review->created_at ? $review->created_at->toISOString() : null,
                    'updatedAt' => $review->updated_at ? $review->updated_at->toISOString() : null,
                    'hostResponse' => $hostResponse,
                    'listing' => [
                        'id' => (string) $review->listing->id,
                        'title' => $review->listing->title,
                        'images' => $review->listing->images ? $review->listing->images->pluck('image_url')->toArray() : [],
                        'location' => $review->listing->location,
                        'hostName' => $review->listing->user->name ?? 'Unknown Host'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => $formattedReviews,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'hasMore' => ($offset + $limit) < $total
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reviews/pending
     * Fetch trips eligible for review submission
     */
    public function pending(Request $request)
    {
        try {
            $user = Auth::user();
            $reviewDeadlineDays = 30;

            // Get completed bookings that don't have reviews yet
            $eligibleBookings = Booking::with(['listing:id,title,location', 'listing.images:id,listing_id,image_url', 'listing.user:id,name'])
                ->where('user_id', $user->id)
                ->where('booking_status', 'completed')
                ->whereDoesntHave('userReviews')
                ->where('end_date', '>=', Carbon::now()->subDays($reviewDeadlineDays))
                ->get();

            $pendingReviews = $eligibleBookings->map(function ($booking) use ($reviewDeadlineDays) {
                $reviewDeadline = Carbon::parse($booking->end_date)->addDays($reviewDeadlineDays);
                $canReview = Carbon::now()->lte($reviewDeadline);

                return [
                    'tripId' => (string) $booking->id,
                    'listingId' => (string) $booking->listing_id,
                    'checkoutDate' => Carbon::parse($booking->end_date)->toISOString(),
                    'reviewDeadline' => $reviewDeadline->toISOString(),
                    'canReview' => $canReview,
                    'listing' => [
                        'id' => (string) $booking->listing->id,
                        'title' => $booking->listing->title,
                        'images' => $booking->listing->images ? $booking->listing->images->pluck('image_url')->toArray() : [],
                        'location' => $booking->listing->location,
                        'hostName' => $booking->listing->user->name ?? 'Unknown Host'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'pendingReviews' => $pendingReviews
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/reviews
     * Submit new review
     */
    public function store(StoreGuestReviewRequest $request)
    {
        try {
            $user = Auth::user();

            $review = UserReview::create([
                'user_id' => $user->id,
                'listing_id' => $request->listingId,
                'trip_id' => $request->tripId,
                'rating' => $request->rating,
                'review' => $request->comment,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => [
                    'id' => (string) $review->id,
                    'status' => $review->status
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/reviews/{reviewId}
     * Update existing review (drafts only)
     */
    public function update(UpdateGuestReviewRequest $request, $reviewId)
    {
        try {
            $user = Auth::user();
            
            $review = UserReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            $review->update([
                'rating' => $request->rating,
                'review' => $request->comment,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => [
                    'id' => (string) $review->id,
                    'status' => $review->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/reviews/{reviewId}
     * Delete draft review
     */
    public function destroy($reviewId)
    {
        try {
            $user = Auth::user();

            $review = UserReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            if ($review->isPublished()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Published reviews cannot be deleted'
                ], 400);
            }

            $review->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/reviews/{reviewId}/publish
     * Publish draft review
     */
    public function publish($reviewId)
    {
        try {
            $user = Auth::user();

            $review = UserReview::with(['listing:id,title,location', 'listing.listingImages:id,listing_id,image_url'])
                ->where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            if ($review->isPublished()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review is already published'
                ], 400);
            }

            $review->update(['status' => 'published']);

            $hostResponse = null;
            if ($review->hasHostResponse()) {
                $hostResponse = [
                    'comment' => $review->host_response_comment,
                    'createdAt' => $review->host_response_created_at->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Review published successfully',
                'data' => [
                    'id' => (string) $review->id,
                    'listingId' => (string) $review->listing_id,
                    'tripId' => (string) $review->trip_id,
                    'rating' => $review->rating,
                    'comment' => $review->review,
                    'status' => $review->status,
                    'createdAt' => $review->created_at->toISOString(),
                    'updatedAt' => $review->updated_at->toISOString(),
                    'hostResponse' => $hostResponse,
                    'listing' => [
                        'id' => (string) $review->listing->id,
                        'title' => $review->listing->title,
                        'images' => $review->listing->listingImages->pluck('image_url')->toArray(),
                        'location' => $review->listing->location,
                        'hostName' => $review->listing->user->name ?? 'Unknown Host'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
