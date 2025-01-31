<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stays')->insert([
            [
                'user_id' => 1,
                'host_id' => 1,
                'property_id' => 1,
                'title' => 'Luxury Apartment in Lusaka',
                'description' => 'A modern and luxurious apartment in the heart of Lusaka, perfect for business travelers and vacationers.',
                'about_this_place' => 'A modern and luxurious apartment in the heart of Lusaka, perfect for business travelers and vacationers.',
                'starting' => Carbon::now()->format('Y-m-d'),
                'ending' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'max_guests' => 4,
                'total_nights' => 10,
                'price_per_night' => 120.00,
                'total_price' => 1200.00,
                'amenities' => json_encode(['WiFi', 'Air Conditioning', 'Swimming Pool', 'Parking', 'Kitchen']),
                'images' => json_encode([
                    'https://example.com/images/stay1_1.jpg',
                    'https://example.com/images/stay1_2.jpg',
                    'https://example.com/images/stay1_3.jpg'
                ]),
                'videos' => json_encode([
                    'https://example.com/videos/stay1.mp4'
                ]),
                'is_available' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 2,
                'host_id' => 2,
                'property_id' => 2,
                'title' => 'Cozy Cottage in Livingstone',
                'description' => 'A peaceful and cozy cottage near Victoria Falls, ideal for nature lovers.',
                'about_this_place' => 'A peaceful and cozy cottage near Victoria Falls, ideal for nature lovers.',
                'starting' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'ending' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'max_guests' => 2,
                'total_nights' => 10,
                'price_per_night' => 80.00,
                'total_price' => 800.00,
                'amenities' => json_encode(['WiFi', 'Garden', 'BBQ Grill', 'Fireplace']),
                'images' => json_encode([
                    'https://example.com/images/stay2_1.jpg',
                    'https://example.com/images/stay2_2.jpg'
                ]),
                'videos' => json_encode([
                    'https://example.com/videos/stay2.mp4'
                ]),
                'is_available' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
