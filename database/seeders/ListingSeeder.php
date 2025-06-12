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
                $imageUrls = [
                    'https://images.pamgolding.co.za/content/properties/202407/2137086/h/2137086_h_22.jpg?w=600&quality=75',
                    'https://www.davikrealestate.com/uploads/7/6/7/4/7674786/salama-park-house-picture-1_1_orig.jpg',
                    'https://real-estate-zambia.beforward.jp/wp-content/uploads/2023/04/1.png',
                    'https://images.pamgolding.co.za/content/properties/202107/1924732/h/1924732_h_9.jpg?w=600&quality=75',
                    'https://images.pamgolding.co.za/content/properties/202206/2010186/h/2010186_h_21.jpg?w=600&quality=75',
                    'https://zambian.estate/storage/files/zm/17283/thumb-816x460-1b21e0af52c3bf051e4cdbcfab0fcc6c.jpg',
                    'https://i.ytimg.com/vi/cwoH2Ek5Klg/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLCxxcH0Qv_dsaRF2c2Sp0ahPWfotQ',
                    'https://www.davikrealestate.com/uploads/7/6/7/4/7674786/img-20180825-wa0007-1_orig.jpg',
                    'https://real-estate-zambia.beforward.jp/wp-content/uploads/2025/01/475280174_930028152445003_7546606997172139654_n-592x444.jpg',
                    'https://images.prop24.com/313802546/Crop600x400',
                    'https://zambian.estate/storage/files/zm/19784/thumbnails/816x460-89793714fdfe05e6b7e69eab940232d0.jpg',
                    'https://res.cloudinary.com/dhsjpmqz9/images/f_auto,q_auto,w_700,h_460,c_fill,g_auto/4_Bedroom_House_for_Sale_in_Silverest_-_K2_000_000_3ZA1515043_0_d4hgi0/pam-golding-properties-ckd7echwd00009smb6m96i6ej.jpg',
                    'https://images.pamgolding.co.za/content/properties/202404/2124839/h/2124839_h_1.jpg?w=600&quality=75',
                    'https://res.cloudinary.com/dhsjpmqz9/images/f_auto,q_auto,w_700,h_460,c_fill,g_auto/3_Bedroom_House_for_Rent_in_Leopards_Hill_Lusaka_-_1_700_per_month_RL2125_1_elflsn/homenet-cl5qumges327409jtqjola763.jpg',
                    'https://www.myroof.co.za/prop_static/MR640583/p/b/12339279.jpg',
                    'https://images.prop24.com/356453066',
                    'https://images.prop24.com/307727869/Crop600x400',
                    'https://images.prop24.com/349608138/Crop600x400',
                    'https://images.prop24.com/352062360/Crop600x400',
                    'https://i0.wp.com/www.gorgeousunknown.com/wp-content/uploads/2020/04/zambia-things-to-do.jpg',
                    'https://www.travelanddestinations.com/wp-content/uploads/2020/03/Skyline-of-Lusaka-city-at-night.jpg',
                    'https://thetravelblog.at/wp-content/uploads/2023/07/2023-07-wild-dogs-lodge-lusaka-zambia-by-marion-payr-12.jpg',
                    'https://a0.muscache.com/im/pictures/hosting/Hosting-1194286374577915478/original/f10e1c64-e095-4ae7-97bb-0aff8448e5dc.jpeg?im_w=720',
                    'https://listing.pamgolding.co.za/images/properties/202311/2103642/H/2103642_H_20.jpg',
                    'https://a0.muscache.com/im/pictures/hosting/Hosting-U3RheVN1cHBseUxpc3Rpbmc6MzIzODk3Mjc%3D/original/e33515de-909f-42b7-80b8-80e90ce1b373.jpeg?im_w=720',
                    'https://cf.bstatic.com/xdata/images/hotel/max1024x768/420973192.webp?k=87e7c09947377336b4882c39a1bb1b94f567b7fa828b710cb0b6929cea4bf3e4&o=',
                    'https://upload.wikimedia.org/wikipedia/commons/c/c4/Zambia_Lusaka_Missini_Krzysztof_B%C5%82a%C5%BCyca_2011.jpg',
                    'https://cloudfront.safaribookings.com/blog/2022/05/05-top-10-things-to-do-in-zambia-BW-1600px.jpg',
                    'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/27/f3/89/c9/lusaka-legacy-resort.jpg?w=1200&h=-1&s=1',
                    'https://www.southluangwa.com/assets/img/luxury-zambia-safari-luangwa-safari-house.jpg',
                ];

                $numImages = $faker->numberBetween(1, 3); // Reduced to max 3 since we have 3 specific images
                $selectedImages = $faker->randomElements($imageUrls, $numImages);

                foreach ($selectedImages as $index => $imageUrl) {
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_url' => $imageUrl,
                        'is_primary' => $index === 0 // First image is primary
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
