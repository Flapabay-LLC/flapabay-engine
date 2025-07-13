<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'host_id' => 1111,
            'fname' => 'Mika',
            'lname' => 'Kovac',
            'email' => 'mikakovac@gmail.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);

        User::create([
            'fname' => 'Sarah',
            'lname' => 'Kovac',
            'email' => 'skovac234@example.com',
            'phone' => '1234007890',
            'password' => bcrypt('password2'),
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);

        User::create([
            'host_id' => 1320,
            'fname' => 'Daniel',
            'lname' => 'Phiri',
            'email' => 'danp10@example.com',
            'phone' => '0987654321',
            'password' => bcrypt('password3'),
            'otp' => '654321',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);

        // Add more users for more chat combinations
        User::create([
            'fname' => 'Emma',
            'lname' => 'Johnson',
            'email' => 'emma.johnson@example.com',
            'phone' => '5551234567',
            'password' => bcrypt('password4'),
            'otp' => '789012',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);

        User::create([
            'fname' => 'Michael',
            'lname' => 'Brown',
            'email' => 'michael.brown@example.com',
            'phone' => '5559876543',
            'password' => bcrypt('password5'),
            'otp' => '345678',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);

        User::create([
            'host_id' => 2456,
            'fname' => 'Lisa',
            'lname' => 'Wang',
            'email' => 'lisa.wang@example.com',
            'phone' => '5554567890',
            'password' => bcrypt('password6'),
            'otp' => '901234',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
            'currency'=>'ZMW'
        ]);
    }
}
