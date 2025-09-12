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
        $userReviews = [
            [
                'user_id' => 1,
                'listing_id' => 1,
                'trip_id' => 1, // Assuming booking with ID 1 exists
                'rating' => 5,
                'review' => 'Amazing place! Loved the experience, the listing was exactly as described, and the hosts were very welcoming.',
                'status' => 'published',
                'host_response_comment' => 'Thank you so much for the wonderful review! We\'re thrilled you enjoyed your stay.',
                'host_response_created_at' => now()->subDays(2),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 2,
                'listing_id' => 2,
                'trip_id' => 2, // Assuming booking with ID 2 exists
                'rating' => 4,
                'review' => 'Great stay, but could use a bit more variety in amenities. Overall, very comfortable and clean.',
                'status' => 'published',
                'host_response_comment' => 'Thanks for your feedback! We\'re working on adding more amenities for future guests.',
                'host_response_created_at' => now()->subDays(1),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 3,
                'listing_id' => 3,
                'trip_id' => 3, // Assuming booking with ID 3 exists
                'rating' => 3,
                'review' => 'Good location, but there were some issues with cleanliness. Could have been better.',
                'status' => 'published',
                'host_response_comment' => null, // No host response yet
                'host_response_created_at' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'user_id' => 4,
                'listing_id' => 1,
                'trip_id' => 4,
                'rating' => 5,
                'review' => 'Absolutely perfect! The location was ideal, the place was spotless, and the host was incredibly responsive.',
                'status' => 'published',
                'host_response_comment' => 'We\'re so happy you had a perfect stay! You\'re welcome back anytime.',
                'host_response_created_at' => now()->subHours(12),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(12),
            ],
            [
                'user_id' => 5,
                'listing_id' => 2,
                'trip_id' => 5,
                'rating' => 2,
                'review' => 'The place didn\'t match the photos. Several amenities were not working and communication was poor.',
                'status' => 'published',
                'host_response_comment' => 'We apologize for the issues during your stay. We\'ve addressed the problems and updated our listing.',
                'host_response_created_at' => now()->subDays(3),
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => 6,
                'listing_id' => 4,
                'trip_id' => 6,
                'rating' => 4,
                'review' => 'Nice place with great views. The check-in process was smooth and the host provided helpful local recommendations.',
                'status' => 'draft', // Draft review not yet published
                'host_response_comment' => null,
                'host_response_created_at' => null,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'user_id' => 7,
                'listing_id' => 5,
                'trip_id' => 7,
                'rating' => 5,
                'review' => 'Outstanding experience! Everything was exactly as advertised. The host went above and beyond to ensure our comfort.',
                'status' => 'published',
                'host_response_comment' => null, // Host hasn\'t responded yet
                'host_response_created_at' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ];

        foreach ($userReviews as $review) {
            DB::table('user_reviews')->insert($review);
        }
    }
}
