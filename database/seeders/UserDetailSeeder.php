<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
            $userDetails = [
                [
                    'user_id' => 1,
                    'bio' => 'A passionate traveler and food enthusiast. Love exploring new cities and cultures.',
                    'live_in' => 'New York',
                    'contact_email' => 'user1@example.com',
                    'phone' => '123-456-7890',
                    'phone_2' => '987-654-3210',
                    'languages' => json_encode(['English', 'Spanish']),
                    'website' => 'https://www.user1website.com',
                    'skype' => 'user1_skype',
                    'facebook' => 'https://facebook.com/user1',
                    'twitter' => 'https://twitter.com/user1',
                    'linkedin' => 'https://linkedin.com/in/user1',
                    'youtube' => 'https://youtube.com/user/user1',
                    'profile_picture_url' => 'https://example.com/profile1.jpg',
                ],
                [
                    'user_id' => 2,
                    'bio' => 'An avid photographer with a love for capturing nature and wildlife.',
                    'live_in' => 'Los Angeles',
                    'contact_email' => 'user2@example.com',
                    'phone' => '234-567-8901',
                    'phone_2' => '876-543-2109',
                    'languages' => json_encode(['English', 'French']),
                    'website' => 'https://www.user2website.com',
                    'skype' => 'user2_skype',
                    'facebook' => 'https://facebook.com/user2',
                    'twitter' => 'https://twitter.com/user2',
                    'linkedin' => 'https://linkedin.com/in/user2',
                    'youtube' => 'https://youtube.com/user/user2',
                    'profile_picture_url' => 'https://example.com/profile2.jpg',
                ],
                // Add more user details as needed
            ];

            foreach ($userDetails as $userDetail) {
                DB::table('user_details')->insert($userDetail);
            }

    }
}
