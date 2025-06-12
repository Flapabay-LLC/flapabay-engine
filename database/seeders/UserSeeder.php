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
            'fname' => 'Mika',
            'lname' => 'Kovac',
            'email' => 'mikakovac@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => now(),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://example.com/photo.jpg',
        ]);

        User::create([
            'host_id' => 1020,
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
        ]);
    }
}
