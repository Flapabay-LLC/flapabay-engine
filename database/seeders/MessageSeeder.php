<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\Chat;
use App\Models\User;
use Faker\Factory as Faker;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all chats with user information
        $chats = Chat::with(['user1', 'user2'])->get();
        
        if ($chats->isEmpty()) {
            $this->command->error('No chats found. Please run ChatSeeder first.');
            return;
        }

        // Sample messages for guests (regular users)
        $guestMessages = [
            'Hi there! How are you doing?',
            'I have a question about your property.',
            'What time is check-in available?',
            'Is the place pet-friendly?',
            'Do you have parking available?',
            'What\'s the cancellation policy?',
            'Can I book for next weekend?',
            'Is the kitchen fully equipped?',
            'What\'s the WiFi password?',
            'Are there any restaurants nearby?',
            'Can you recommend local attractions?',
            'Is the area safe for families?',
            'What\'s the best way to get there?',
            'Do you provide towels and linens?',
            'Is there air conditioning?',
            'Can I have an early check-in?',
            'What\'s the latest check-out time?',
            'Are there any house rules I should know?',
            'Can I bring additional guests?',
            'Perfect! I\'ll book it now.',
            'Looking forward to my stay!',
            'Thanks for all the information.',
            'See you soon!',
            'Have a great day!'
        ];

        // Sample messages for hosts
        $hostMessages = [
            'Hi! Thanks for your interest in my property.',
            'Check-in is available from 3 PM onwards.',
            'Yes, the place is pet-friendly with a small fee.',
            'We have free parking available on the premises.',
            'Cancellation is free up to 24 hours before check-in.',
            'Absolutely! Next weekend is available.',
            'Yes, the kitchen is fully equipped with everything you need.',
            'The WiFi password is provided upon check-in.',
            'There are several great restaurants within walking distance.',
            'I\'d be happy to recommend some local attractions!',
            'Yes, this is a very safe family-friendly neighborhood.',
            'The best way to get here is by taxi or Uber.',
            'Yes, we provide fresh towels and linens.',
            'Yes, there\'s central air conditioning throughout.',
            'Early check-in can be arranged if the place is ready.',
            'Check-out time is 11 AM.',
            'Just the standard house rules - no smoking, no parties.',
            'Additional guests are welcome with prior notice.',
            'Great! I\'ll send you the booking confirmation.',
            'Looking forward to hosting you!',
            'You\'re very welcome! Let me know if you need anything else.',
            'See you at check-in!',
            'Have a wonderful day!'
        ];

        // Create messages for each chat
        foreach ($chats as $chat) {
            // Create 3-8 messages per chat
            $messageCount = $faker->numberBetween(3, 8);
            $parentMessages = []; // Track messages that can have replies
            
            for ($i = 0; $i < $messageCount; $i++) {
                $isReply = $faker->boolean(30) && !empty($parentMessages); // 30% chance of being a reply
                $sender = $faker->randomElement([$chat->user1, $chat->user2]);
                $receiver = $sender->id === $chat->user1_id ? $chat->user2 : $chat->user1;
                
                // Choose message based on whether sender is a host or guest
                $isHost = !is_null($sender->host_id);
                $messageText = $isHost ? $faker->randomElement($hostMessages) : $faker->randomElement($guestMessages);
                
                $messageData = [
                    'chat_id' => $chat->id,
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'message' => $messageText,
                    'is_read' => $faker->boolean(70), // 70% chance of being read
                    'deleted_for_sender' => false,
                    'deleted_for_receiver' => false,
                ];
                
                // Add parent_message_id for replies
                if ($isReply) {
                    $messageData['parent_message_id'] = $faker->randomElement($parentMessages);
                }
                
                $message = Message::create($messageData);
                
                // Add to parent messages for potential replies (only if not a reply itself)
                if (!$isReply) {
                    $parentMessages[] = $message->id;
                }
                
                // Add some delay between messages to simulate real conversation
                $message->created_at = now()->subMinutes($faker->numberBetween(1, 1440)); // 1 minute to 24 hours ago
                $message->save();
            }
        }

        $this->command->info('Messages seeded successfully!');
        $this->command->info('- Host messages: Property-related responses');
        $this->command->info('- Guest messages: Property-related questions');
    }
} 