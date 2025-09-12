<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if we have the required bookings
        $requiredBookingIds = [11, 12, 13];
        $existingBookings = DB::table('bookings')
            ->whereIn('id', $requiredBookingIds)
            ->pluck('id')
            ->toArray();
        
        if (count($existingBookings) < count($requiredBookingIds)) {
            echo "Required bookings not found. Expected: " . implode(', ', $requiredBookingIds) . "\n";
            echo "Found: " . implode(', ', $existingBookings) . "\n";
            return;
        }

        $userReviews = [
            [
                'user_id' => 7,
                'listing_id' => 1,
                'trip_id' => 11,
                'rating' => 5,
                'review' => 'Amazing beachfront villa! Loved the experience, the listing was exactly as described, and the hosts were very welcoming.',
                'status' => 'published',
                'host_response_comment' => 'Thank you so much for the wonderful review! We\'re thrilled you enjoyed your stay.',
                'host_response_created_at' => now()->subDays(2),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 7,
                'listing_id' => 2,
                'trip_id' => 12, // This matches another completed booking for user 7
                'rating' => 4,
                'review' => 'Great room in Lusaka Woodlands, but could use a bit more variety in amenities. Overall, very comfortable and clean.',
                'status' => 'published',
                'host_response_comment' => 'Thanks for your feedback! We\'re working on adding more amenities for future guests.',
                'host_response_created_at' => now()->subDays(1),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
        ];
        
        // Only add more reviews if we have enough bookings
        if (count($existingBookings) >= 3) {
            $userReviews[] = [
                'user_id' => 7,
                'listing_id' => 3,
                'trip_id' => 13, // This matches the third completed booking for user 7
                'rating' => 3,
                'review' => 'Good location at the Peaceful Garden Bungalow, but there were some issues with cleanliness. Could have been better.',
                'status' => 'draft', // Draft review for testing
                'host_response_comment' => null,
                'host_response_created_at' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ];
        }

        foreach ($userReviews as $review) {
            DB::table('user_reviews')->insert($review);
        }
    }
}
