<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use App\Models\ListingImage;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all hosts
        $hosts = User::whereNotNull('host_id')->get();
        
        if ($hosts->isEmpty()) {
            $this->command->error('No hosts found. Please create at least one user with host_id.');
            return;
        }

        // Create 30 listings
        for ($i = 0; $i < 30; $i++) {
            DB::beginTransaction();
            try {
                // Create property
                $property = Property::create([
                    'title' => $faker->sentence(3),
                    'description' => $faker->paragraphs(3, true),
                    'location' => $faker->address,
                    'address' => $faker->streetAddress,
                    'latitude' => $faker->latitude,
                    'longitude' => $faker->longitude,
                    'check_in_hour' => '14:00',
                    'check_out_hour' => '11:00',
                    'num_of_guests' => $faker->numberBetween(1, 10),
                    'num_of_children' => $faker->numberBetween(0, 5),
                    'maximum_guests' => $faker->numberBetween(2, 12),
                    'allow_extra_guests' => $faker->boolean,
                    'neighborhood_area' => $faker->city,
                    'country' => $faker->country,
                    'show_contact_form_instead_of_booking' => false,
                    'allow_instant_booking' => true,
                    'currency' => 'USD',
                    'price' => $faker->numberBetween(50, 500),
                    'price_per_night' => $faker->numberBetween(50, 500),
                    'additional_guest_price' => $faker->numberBetween(10, 50),
                    'children_price' => $faker->numberBetween(5, 25),
                    'amenities' => json_encode($faker->randomElements(['WiFi', 'Kitchen', 'Pool', 'Parking', 'Air Conditioning'], 3)),
                    'house_rules' => json_encode($faker->randomElements(['No smoking', 'No pets', 'No parties'], 2)),
                    'video_link' => json_encode(['url' => 'https://www.youtube.com/watch?v=' . $faker->uuid]),
                    'property_type_id' => json_encode([$faker->numberBetween(1, 5)]),
                    'category_id' => json_encode([$faker->numberBetween(1, 5)]),
                    'place_items' => json_encode($faker->randomElements(['Bed', 'TV', 'Sofa', 'Table'], 3)),
                    'verified' => true,
                    'about_place' => $faker->paragraph,
                    'host_type' => $faker->randomElement(['Private Individual', 'Business']),
                    'num_of_bedrooms' => $faker->numberBetween(1, 5),
                    'num_of_bathrooms' => $faker->numberBetween(1, 3),
                    'num_of_quarters' => $faker->numberBetween(0, 2),
                    'has_unallocated_rooms' => $faker->boolean,
                    'first_reserver' => $faker->name
                ]);

                // Create listing
                $listing = Listing::create([
                    'host_id' => $hosts->random()->id,
                    'title' => $property->title,
                    'property_id' => $property->id,
                    'category_id' => $faker->numberBetween(1, 5),
                    'status' => true,
                    'published_at' => now(),
                    'cancellation_policy' => false,
                    'is_completed' => true,
                    'listing_type' => $faker->randomElement(['stay', 'experience'])
                ]);

                // Create sample images
                $numImages = $faker->numberBetween(3, 6);
                for ($j = 0; $j < $numImages; $j++) {
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_url' => 'https://picsum.photos/800/600?random=' . $faker->unique()->numberBetween(1, 1000),
                        'is_primary' => $j === 0 // First image is primary
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Failed to create listing: " . $e->getMessage());
            }
        }

        $this->command->info('30 listings seeded successfully!');
    }
}
