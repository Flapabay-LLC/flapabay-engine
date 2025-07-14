<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Thread;
use App\Models\Message;
use App\Models\User;
use App\Models\Listing;
use Faker\Factory as Faker;

class ThreadSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $hosts = User::whereNotNull('host_id')->get();
        $guests = User::whereNull('host_id')->get();
        $listings = Listing::all();

        if ($hosts->isEmpty() || $guests->isEmpty() || $listings->isEmpty()) {
            $this->command->error('Need hosts, guests, and listings to seed threads.');
            return;
        }

        $threadCount = min(10, $hosts->count() * $guests->count());
        $usedCombos = [];
        for ($i = 0; $i < $threadCount; $i++) {
            $host = $hosts->random();
            $guest = $guests->random();
            $listing = $listings->random();
            // Alternate between 'listing' and 'experience' context_type
            $contextType = $i % 2 === 0 ? 'listing' : 'experience';
            $comboKey = $guest->id.'-'.$host->id.'-'.$contextType.'-'.$listing->id;
            if (isset($usedCombos[$comboKey])) continue;
            $usedCombos[$comboKey] = true;
            $thread = Thread::create([
                'guest_id' => $guest->id,
                'host_id' => $host->id,
                'thread_type' => 'inquiry',
                'context_type' => $contextType,
                'context_id' => $listing->id,
                'status' => 'active',
            ]);
            // Seed 2-3 normal text messages
            $participants = [$guest, $host];
            for ($j = 0; $j < 2; $j++) {
                $sender = $participants[$j % 2];
                $receiver = $participants[($j + 1) % 2];
                Message::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'message' => $faker->sentence(8) . ($contextType === 'experience' ? ' (experience)' : ' (listing)'),
                    'type' => 'text',
                    'is_read' => $faker->boolean(70),
                    'deleted_for_sender' => false,
                    'deleted_for_receiver' => false,
                ]);
            }
            // Pre-approval (host to guest)
            Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $host->id,
                'receiver_id' => $guest->id,
                'message' => 'You are pre-approved to book this ' . $contextType . '!',
                'type' => 'pre_approval',
                'meta' => ['booking_id' => $faker->randomNumber(5), 'expires_at' => now()->addDays(2)],
                'is_read' => false,
                'deleted_for_sender' => false,
                'deleted_for_receiver' => false,
            ]);
            // Special offer (host to guest)
            Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $host->id,
                'receiver_id' => $guest->id,
                'message' => 'Special offer: 10% off for this weekend on this ' . $contextType . '!',
                'type' => 'special_offer',
                'meta' => ['discount' => '10%', 'valid_until' => now()->addDays(1)],
                'is_read' => false,
                'deleted_for_sender' => false,
                'deleted_for_receiver' => false,
            ]);
            // System message
            Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $host->id, // Use host as sender for system messages
                'receiver_id' => $guest->id,
                'message' => 'Your booking for this ' . $contextType . ' has been confirmed!',
                'type' => 'system',
                'meta' => ['event' => 'booking_confirmed', 'booking_id' => $faker->randomNumber(5)],
                'is_read' => false,
                'deleted_for_sender' => false,
                'deleted_for_receiver' => false,
            ]);
            // Attachment (guest to host)
            Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $guest->id,
                'receiver_id' => $host->id,
                'message' => 'Please see the attached document for this ' . $contextType . '.',
                'type' => 'attachment',
                'meta' => [
                    'attachments' => [
                        ['url' => $faker->imageUrl(), 'name' => 'photo.jpg', 'type' => 'image/jpeg', 'size' => 123456],
                        ['url' => $faker->url(), 'name' => 'contract.pdf', 'type' => 'application/pdf', 'size' => 456789],
                    ]
                ],
                'is_read' => false,
                'deleted_for_sender' => false,
                'deleted_for_receiver' => false,
            ]);
            // Saved reply (host to guest)
            $savedReply = $faker->sentence(10) . ' (' . $contextType . ')';
            \App\Models\SavedReply::firstOrCreate([
                'host_id' => $host->id,
                'reply_text' => $savedReply,
            ]);
            Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $host->id,
                'receiver_id' => $guest->id,
                'message' => $savedReply,
                'type' => 'saved_reply',
                'meta' => ['saved_reply_id' => \App\Models\SavedReply::where('host_id', $host->id)->where('reply_text', $savedReply)->first()->id],
                'is_read' => false,
                'deleted_for_sender' => false,
                'deleted_for_receiver' => false,
            ]);
        }
        $this->command->info('Thread-based chat data seeded successfully!');
    }
} 