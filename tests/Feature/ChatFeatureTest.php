<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Thread;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\MessageSent;
use App\Events\MessageRead;
use Tests\TestCase;

class ChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_thread_with_host()
    {
        $guest = User::factory()->create(['host_id' => null]);
        $host = User::factory()->create(['host_id' => 1]);
        $this->actingAs($guest);
        $response = $this->postJson('/api/v1/chat/start', [
            'host_id' => $host->id,
            'message' => 'Hello, host!',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('threads', [
            'guest_id' => $guest->id,
            'host_id' => $host->id,
        ]);
    }

    public function test_participant_can_send_message_in_thread()
    {
        $guest = User::factory()->create(['host_id' => null]);
        $host = User::factory()->create(['host_id' => 1]);
        $thread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id]);
        $this->actingAs($guest);
        $response = $this->postJson('/api/v1/chat/thread/message', [
            'thread_id' => $thread->id,
            'message' => 'Test message',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_id' => $guest->id,
            'message' => 'Test message',
        ]);
    }

    public function test_non_participant_cannot_send_message()
    {
        $guest = User::factory()->create(['host_id' => null]);
        $host = User::factory()->create(['host_id' => 1]);
        $other = User::factory()->create(['host_id' => null]);
        $thread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id]);
        $this->actingAs($other);
        $response = $this->postJson('/api/v1/chat/thread/message', [
            'thread_id' => $thread->id,
            'message' => 'Should not work',
        ]);
        $response->assertStatus(403);
    }

    public function test_message_sent_event_is_emitted()
    {
        Event::fake([MessageSent::class]);
        $guest = User::factory()->create(['host_id' => null]);
        $host = User::factory()->create(['host_id' => 1]);
        $thread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id]);
        $this->actingAs($guest);
        $this->postJson('/api/v1/chat/thread/message', [
            'thread_id' => $thread->id,
            'message' => 'Emit event',
        ]);
        Event::assertDispatched(MessageSent::class);
    }

    public function test_can_filter_threads_by_category()
    {
        $guest = User::factory()->create(['host_id' => null]);
        $host = User::factory()->create(['host_id' => 1]);
        $homesThread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id, 'category' => 'Homes']);
        $expThread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id, 'category' => 'Experiences']);
        $travelThread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id, 'category' => 'Traveling']);
        $supportThread = Thread::factory()->create(['guest_id' => $guest->id, 'host_id' => $host->id, 'category' => 'Support']);
        $this->actingAs($guest);
        // All
        $response = $this->getJson('/api/v1/chat/threads?category=All');
        $response->assertStatus(200)->assertJsonFragment(['category' => 'Homes'])
            ->assertJsonFragment(['category' => 'Experiences'])
            ->assertJsonFragment(['category' => 'Traveling'])
            ->assertJsonFragment(['category' => 'Support']);
        // Homes
        $response = $this->getJson('/api/v1/chat/threads?category=Homes');
        $response->assertStatus(200)->assertJsonFragment(['category' => 'Homes'])
            ->assertJsonMissing(['category' => 'Experiences'])
            ->assertJsonMissing(['category' => 'Traveling'])
            ->assertJsonMissing(['category' => 'Support']);
        // Experiences
        $response = $this->getJson('/api/v1/chat/threads?category=Experiences');
        $response->assertStatus(200)->assertJsonFragment(['category' => 'Experiences'])
            ->assertJsonMissing(['category' => 'Homes'])
            ->assertJsonMissing(['category' => 'Traveling'])
            ->assertJsonMissing(['category' => 'Support']);
        // Traveling
        $response = $this->getJson('/api/v1/chat/threads?category=Traveling');
        $response->assertStatus(200)->assertJsonFragment(['category' => 'Traveling'])
            ->assertJsonMissing(['category' => 'Homes'])
            ->assertJsonMissing(['category' => 'Experiences'])
            ->assertJsonMissing(['category' => 'Support']);
        // Support
        $response = $this->getJson('/api/v1/chat/threads?category=Support');
        $response->assertStatus(200)->assertJsonFragment(['category' => 'Support'])
            ->assertJsonMissing(['category' => 'Homes'])
            ->assertJsonMissing(['category' => 'Experiences'])
            ->assertJsonMissing(['category' => 'Traveling']);
    }

    // TODO: Add tests for MessageRead, Typing, PresenceUpdated events
    // TODO: Add tests for socket channel authorization (if possible)
    // TODO: Add tests for pagination, deletion, and role-specific endpoints
} 