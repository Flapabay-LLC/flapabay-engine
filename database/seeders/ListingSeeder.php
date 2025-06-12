<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all host users (users with host_id)
        $hosts = User::whereNotNull('host_id')->get();
        
        if ($hosts->isEmpty()) {
            $this->command->error('No hosts found. Please seed users first.');
            return;
        }

        // Sample property types
        $propertyTypes = [1, 2, 3, 4, 5];
        // $propertyTypes = ['Featured', 'Guest Favorite', 'Others'];

        // Sample listing types
        $listingType = ['Stays', 'Experiences'];
        
        // Sample amenities
        $amenities = [
            'WiFi', 'Air Conditioning', 'Kitchen', 'TV', 'Pool', 'Gym',
            'Parking', 'Washer', 'Dryer', 'Elevator', 'Hot Tub', 'BBQ Grill',
            'Fireplace', 'Security System', 'Garden'
        ];

        // Sample house rules
        $houseRules = [
            'No smoking', 'No pets', 'No parties', 'Quiet hours after 10 PM',
            'No shoes inside', 'No loud music', 'No unregistered guests'
        ];

        // Sample counties
        $counties = [
            'Los Angeles County', 'Cook County', 'Harris County', 'Maricopa County',
            'San Diego County', 'Orange County', 'Kings County', 'Miami-Dade County',
            'Dallas County', 'Riverside County'
        ];

        // Create 30 listings
        for ($i = 0; $i < 30; $i++) {
            // Create property first
            $property = Property::create([
                'title' => $faker->sentence(3),
                'description' => $faker->paragraphs(3, true),
                'location' => $faker->city,
                'address' => $faker->streetAddress,
                'county' => $faker->randomElement($counties),
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
                'check_in_hour' => '15:00:00',
                'check_out_hour' => '11:00:00',
                'num_of_guests' => $faker->numberBetween(1, 10),
                'num_of_children' => $faker->numberBetween(0, 4),
                'maximum_guests' => $faker->numberBetween(2, 12),
                'allow_extra_guests' => $faker->boolean,
                'neighborhood_area' => $faker->streetName,
                'country' => $faker->country,
                'show_contact_form_instead_of_booking' => $faker->boolean(20),
                'allow_instant_booking' => $faker->boolean(80),
                'currency' => 'USD',
                'price_range' => json_encode(['min' => 50, 'max' => 500]),
                'price' => $faker->numberBetween(50, 500),
                'price_per_night' => $faker->numberBetween(50, 500),
                'additional_guest_price' => $faker->numberBetween(10, 50),
                'children_price' => $faker->numberBetween(5, 25),
                'amenities' => json_encode($faker->randomElements($amenities, $faker->numberBetween(3, 8))),
                'house_rules' => json_encode($faker->randomElements($houseRules, $faker->numberBetween(2, 5))),
                'page' => $faker->numberBetween(1, 10),
                'rating' => $faker->randomFloat(1, 3, 5),
                'favorite' => $faker->boolean(30),
                'images' => json_encode([
                    'https://picsum.photos/800/600?random=' . $i,
                    'https://picsum.photos/800/600?random=' . ($i + 1),
                    'https://picsum.photos/800/600?random=' . ($i + 2)
                ]),
                'video_link' => json_encode(['https://www.youtube.com/watch?v=dQw4w9WgXcQ']),
                'verified' => $faker->boolean(80),
                'property_type_id' => $faker->randomElement($propertyTypes),
            ]);

            // Create listing
            Listing::create([
                'title' => $property->title,
                'property_id' => $property->id,
                'host_id' => $hosts->random()->id,
                'post_levels' => json_encode(['basic' => true]),
                'category_id' => $faker->numberBetween(1, 5),
                'published_at' => Carbon::now()->subDays($faker->numberBetween(1, 30)),
                'status' => $faker->boolean(90),
                'cancellation_policy' => $faker->boolean(70),
                'is_completed' => $faker->boolean(80),
                'listing_type' => $faker->randomElement($listingType),
            ]);
        }

        $this->command->info('30 listings seeded successfully!');
    }
}
