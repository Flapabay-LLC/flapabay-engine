<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Experience;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get user 7
        $user = User::find(7);
        
        if (!$user) {
            $this->command->error('User with ID 7 not found. Please create user 7 first.');
            return;
        }

        // Sample experience data
        $experiences = [
            [
                'title' => 'Victoria Falls Adventure Tour',
                'description' => 'Experience the breathtaking Victoria Falls with a guided tour including helicopter ride, white water rafting, and sunset cruise. Perfect for adventure seekers looking to explore one of the Seven Natural Wonders of the World.',
                'activity_type' => 'adventure',
                'difficulty_level' => 'moderate',
                'duration_hours' => 8,
                'duration_minutes' => 0,
                'price_per_person' => 250.00,
                'minimum_group_size' => 2,
                'maximum_group_size' => 12,
                'location' => 'Victoria Falls, Livingstone, Zambia',
                'what_to_bring' => 'Comfortable walking shoes, sunscreen, hat, camera, swimwear',
                'what_is_included' => 'Professional guide, helicopter ride, rafting equipment, lunch, transportation',
                'what_is_not_included' => 'Personal expenses, gratuities, alcoholic beverages'
            ],
            [
                'title' => 'Lusaka Cultural Walking Tour',
                'description' => 'Discover the vibrant culture of Lusaka through local markets, traditional crafts, and authentic Zambian cuisine. Learn about local history and traditions from knowledgeable local guides.',
                'activity_type' => 'cultural',
                'difficulty_level' => 'easy',
                'duration_hours' => 4,
                'duration_minutes' => 30,
                'price_per_person' => 45.00,
                'minimum_group_size' => 1,
                'maximum_group_size' => 8,
                'location' => 'Lusaka City Center, Zambia',
                'what_to_bring' => 'Comfortable walking shoes, water bottle, small backpack',
                'what_is_included' => 'Local guide, market visits, traditional lunch, cultural demonstrations',
                'what_is_not_included' => 'Personal shopping, transportation to meeting point'
            ],
            [
                'title' => 'South Luangwa Safari Experience',
                'description' => 'Join us for an unforgettable wildlife safari in South Luangwa National Park. Spot elephants, lions, leopards, and hundreds of bird species in their natural habitat.',
                'activity_type' => 'outdoor',
                'difficulty_level' => 'easy',
                'duration_hours' => 12,
                'duration_minutes' => 0,
                'price_per_person' => 180.00,
                'minimum_group_size' => 4,
                'maximum_group_size' => 16,
                'location' => 'South Luangwa National Park, Zambia',
                'what_to_bring' => 'Binoculars, camera with extra batteries, neutral colored clothing, insect repellent',
                'what_is_included' => 'Game drive vehicle, professional guide, park fees, lunch, refreshments',
                'what_is_not_included' => 'Accommodation, dinner, personal items'
            ],
            [
                'title' => 'Traditional Zambian Cooking Class',
                'description' => 'Learn to prepare authentic Zambian dishes including nshima, relish, and traditional vegetables. Enjoy your creations with local beer or traditional drinks.',
                'activity_type' => 'food_drink',
                'difficulty_level' => 'easy',
                'duration_hours' => 3,
                'duration_minutes' => 0,
                'price_per_person' => 35.00,
                'minimum_group_size' => 2,
                'maximum_group_size' => 10,
                'location' => 'Lusaka, Zambia',
                'what_to_bring' => 'Apron (provided if needed), appetite for learning',
                'what_is_included' => 'All ingredients, cooking equipment, recipe cards, meal, beverages',
                'what_is_not_included' => 'Transportation, additional drinks'
            ],
            [
                'title' => 'Kafue River Canoeing Adventure',
                'description' => 'Paddle through the scenic Kafue River, observing wildlife and enjoying the peaceful waters. Suitable for beginners with basic instruction provided.',
                'activity_type' => 'outdoor',
                'difficulty_level' => 'moderate',
                'duration_hours' => 6,
                'duration_minutes' => 0,
                'price_per_person' => 85.00,
                'minimum_group_size' => 2,
                'maximum_group_size' => 12,
                'location' => 'Kafue River, Zambia',
                'what_to_bring' => 'Swimwear, change of clothes, waterproof bag for valuables, sunscreen',
                'what_is_included' => 'Canoe, paddle, life jacket, guide, snacks, water',
                'what_is_not_included' => 'Lunch, transportation from Lusaka'
            ]
        ];

        foreach ($experiences as $experienceData) {
            DB::beginTransaction();
            try {
                // Create the main listing
                $listing = Listing::create([
                    'user_id' => $user->id,
                    'title' => $experienceData['title'],
                    'description' => $experienceData['description'],
                    'location' => $experienceData['location'],
                    'address' => $experienceData['location'],
                    'latitude' => $faker->latitude(-18, -8), // Zambia latitude range
                    'longitude' => $faker->longitude(22, 34), // Zambia longitude range
                    'listing_type' => 'experience',
                    'status' => Listing::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'is_completed' => true,
                    'currency' => 'USD',
                    'price' => $experienceData['price_per_person'],
                    'maximum_guests' => $experienceData['maximum_group_size'],
                    'num_of_guests' => $experienceData['minimum_group_size'],
                    'images' => json_encode([
                        'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=800',
                        'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800',
                        'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800'
                    ]),
                    'amenities' => json_encode(['Professional Guide', 'Safety Equipment', 'Refreshments']),
                    'rating' => $faker->randomFloat(1, 4.0, 5.0),
                    'verified' => true,
                    'featured_status' => $faker->boolean(30) // 30% chance of being featured
                ]);

                // Create the experience details
                Experience::create([
                    'listing_id' => $listing->id,
                    'duration_hours' => $experienceData['duration_hours'],
                    'duration_minutes' => $experienceData['duration_minutes'],
                    'minimum_group_size' => $experienceData['minimum_group_size'],
                    'maximum_group_size' => $experienceData['maximum_group_size'],
                    'activity_type' => $experienceData['activity_type'],
                    'difficulty_level' => $experienceData['difficulty_level'],
                    'physical_activity_level' => $faker->randomElement(['low', 'moderate', 'high']),
                    'minimum_age' => $faker->numberBetween(8, 16),
                    'children_allowed' => true,
                    'available_times' => json_encode(['09:00', '14:00']),
                    'available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
                    'flexible_scheduling' => true,
                    'advance_booking_hours' => 24,
                    'cancellation_hours' => 24,
                    'what_to_bring' => $experienceData['what_to_bring'],
                    'what_is_included' => $experienceData['what_is_included'],
                    'what_is_not_included' => $experienceData['what_is_not_included'],
                    'meeting_point' => $experienceData['location'],
                    'safety_requirements' => 'Basic fitness level required. Follow guide instructions at all times.',
                    'languages_offered' => json_encode(['English', 'Bemba', 'Nyanja']),
                    'price_per_person' => $experienceData['price_per_person'],
                    'group_discount_percentage' => 10.00,
                    'group_discount_threshold' => 6
                ]);

                DB::commit();
                $this->command->info("Created experience: {$experienceData['title']}");
            } catch (\Exception $e) {
                DB::rollback();
                $this->command->error("Failed to create experience: {$experienceData['title']} - {$e->getMessage()}");
            }
        }

        $this->command->info('Experience seeding completed for user 7!');
    }
}