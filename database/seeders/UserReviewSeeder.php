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
                    'property_id' => 1, // Assuming property with ID 1 exists
                    'rating' => 5,
                    'review' => 'Amazing place! Loved the experience, the property was exactly as described, and the hosts were very welcoming.',
                ],
                [
                    'user_id' => 2,
                    'property_id' => 2, // Assuming property with ID 2 exists
                    'rating' => 4,
                    'review' => 'Great stay, but could use a bit more variety in amenities. Overall, very comfortable.',
                ],
                [
                    'user_id' => 3,
                    'property_id' => 3, // Assuming property with ID 3 exists
                    'rating' => 3,
                    'review' => 'Good location, but there were some issues with cleanliness. Could have been better.',
                ],
                // Add more reviews as needed
            ];

            foreach ($userReviews as $review) {
                DB::table('user_reviews')->insert($review);
            }

    }
}
