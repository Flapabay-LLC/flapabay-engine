<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $properties = [
                [
                    'title'=> 'Sweet Home',
                    'description'=> 'Sweet Home Sweet HomeSweet HomeSweet HomeSweet HomeSweet HomeSweet HomeSweet Home',
                    'location'=> 'Lusaka',
                    'address'=> '213 Est Ave. JJ Road, Lusaka',
                    'county'=> 'Zambia',
                    'latitude'=> '12.470039830293833',
                    'longitude'=> '12.76767754545454',

                    'check_out_hour' => '11:00:00',
                    'num_of_guests' => 4,
                    'num_of_children' => 1,
                    'maximum_guests' => 5,
                    'allow_extra_guests' => true,
                    'neighborhood_area' => 'Downtown',
                    'country' => 'US',
                    'show_contact_form_instead_of_booking' => false,
                    'allow_instant_booking' => true,
                    'currency' => 'USD',
                    'price_range' => json_encode(['min' => 100, 'max' => 500]),
                    'price' => 150.00,
                    'price_per_night' => 150.00,
                    'additional_guest_price' => 20.00,
                    'children_price' => 10.00,
                    'amenities' => json_encode(['Wi-Fi', 'Pool', 'Air Conditioning']),
                    'house_rules' => json_encode(['No smoking', 'Pet-friendly']),
                    'page' => 1,
                    'rating' => 4.5,
                    'favorite' => true,
                    'images' => json_encode(['image1.jpg', 'image2.jpg']),
                    'video_link' => json_encode(['https://example.com/video1']),
                    'verified' => true,
                    'tags' => json_encode(['beach', 'luxury', 'family-friendly']),
                    'category_id' => json_encode([1, 2]),
                    'property_type_id' => json_encode([1, 3]),
                ],
                [
                    'title'=> 'Studio Apartment',
                    'description'=> 'Sweet Home Sweet HomeSweet HomeSweet HomeSweet HomeSweet HomeSweet HomeSweet Home',
                    'location'=> 'Ndola',
                    'address'=> '2304 Salama Park, Ndola',
                    'county'=> 'Zambia',
                    'latitude'=> '12.472358830293833',
                    'longitude'=> '12.76767000000454',

                    'check_out_hour' => '10:00:00',
                    'num_of_guests' => 2,
                    'num_of_children' => 0,
                    'maximum_guests' => 2,
                    'allow_extra_guests' => false,
                    'neighborhood_area' => 'Uptown',
                    'country' => 'UK',
                    'show_contact_form_instead_of_booking' => true,
                    'allow_instant_booking' => false,
                    'currency' => 'GBP',
                    'price_range' => json_encode(['min' => 50, 'max' => 300]),
                    'price' => 80.00,
                    'price_per_night' => 80.00,
                    'additional_guest_price' => 15.00,
                    'children_price' => 8.00,
                    'amenities' => json_encode(['Wi-Fi', 'Parking']),
                    'house_rules' => json_encode(['No pets', 'Quiet hours']),
                    'page' => 2,
                    'rating' => 3.8,
                    'favorite' => false,
                    'images' => json_encode(['image3.jpg', 'image4.jpg']),
                    'video_link' => json_encode(['https://example.com/video2']),
                    'verified' => false,
                    'tags' => json_encode(['city-center', 'budget']),
                    'category_id' => json_encode([2, 4]),
                    'property_type_id' => json_encode([2]),
                ],
                // Add more sample properties as needed
            ];

            foreach ($properties as $property) {
                DB::table('properties')->insert($property);
            }

    }
}
