<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class listingReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listingReviews = [
            [
                'user_id' => 1,
                'listing_id' => 1,
                'rating' => 5,
                'review' => 'This listing exceeded all expectations! The photos were accurate and the amenities were top-notch.',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'user_id' => 2,
                'listing_id' => 1,
                'rating' => 4,
                'review' => 'Great location and beautiful space. Minor issues with WiFi but overall excellent stay.',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'user_id' => 3,
                'listing_id' => 2,
                'rating' => 5,
                'review' => 'Perfect for a weekend getaway! Clean, comfortable, and exactly as described.',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            [
                'user_id' => 4,
                'listing_id' => 2,
                'rating' => 3,
                'review' => 'Decent place but could use some updates. The location is convenient though.',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => 5,
                'listing_id' => 3,
                'rating' => 4,
                'review' => 'Nice property with good amenities. The host was responsive and helpful.',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 6,
                'listing_id' => 3,
                'rating' => 5,
                'review' => 'Amazing experience! Would definitely book again. Highly recommend this place.',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($listingReviews as $review) {
            DB::table('listing_reviews')->insert($review);
        }
    }
}
