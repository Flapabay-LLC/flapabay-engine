<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chat;
use App\Models\User;
use Faker\Factory as Faker;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get hosts (users with host_id) and regular users (users without host_id)
        $hosts = User::whereNotNull('host_id')->get();
        $regularUsers = User::whereNull('host_id')->get();
        
        if ($hosts->isEmpty() || $regularUsers->isEmpty()) {
            $this->command->error('Need both hosts (users with host_id) and regular users (users without host_id) to create meaningful chats. Please run UserSeeder first.');
            return;
        }

        $this->command->info("Found {$hosts->count()} hosts and {$regularUsers->count()} regular users.");

        // Create chat threads between hosts and regular users
        $chatCount = min(20, $hosts->count() * $regularUsers->count()); // Don't exceed possible combinations
        
        for ($i = 0; $i < $chatCount; $i++) {
            // Get one host and one regular user
            $host = $hosts->random();
            $regularUser = $regularUsers->random();
            
            // Check if chat already exists between these users
            $existingChat = Chat::where(function($query) use ($host, $regularUser) {
                $query->where(function($q) use ($host, $regularUser) {
                    $q->where('user1_id', $host->id)
                      ->where('user2_id', $regularUser->id);
                })->orWhere(function($q) use ($host, $regularUser) {
                    $q->where('user1_id', $regularUser->id)
                      ->where('user2_id', $host->id);
                });
            })->first();
            
            if (!$existingChat) {
                Chat::create([
                    'user1_id' => $host->id,
                    'user2_id' => $regularUser->id,
                ]);
            }
        }

        $this->command->info('Chat threads seeded successfully!');
        $this->command->info("- Host ↔ Guest chats: {$chatCount}");
    }
} 