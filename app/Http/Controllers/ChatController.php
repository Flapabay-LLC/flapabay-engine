<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageRead;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all messages for a specific chat
     */
    public function getAllChatMessages($chatId)
    {
        try {
            $userId = Auth::id();
            
            // Verify user is part of the chat
            $chat = \App\Models\Chat::where('id', $chatId)
                ->where(function($query) use ($userId) {
                    $query->where('user1_id', $userId)
                        ->orWhere('user2_id', $userId);
                })
                ->firstOrFail();

            $messages = Message::where('chat_id', $chatId)
                ->where(function($query) use ($userId) {
                    $query->where(function($q) use ($userId) {
                        $q->where('sender_id', $userId)
                            ->where('deleted_for_sender', false);
                    })->orWhere(function($q) use ($userId) {
                        $q->where('receiver_id', $userId)
                            ->where('deleted_for_receiver', false);
                    });
                })
                ->with(['sender', 'replies'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark messages as read
            $unread = Message::where('chat_id', $chatId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->get();
            Message::where('chat_id', $chatId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
            // Broadcast MessageRead event for each message
            foreach ($unread as $msg) {
                event(new MessageRead($msg, $userId));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Chat messages fetched successfully',
                'data' => $messages
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch chat messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a new message
     */
    public function sendChatMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string'
            ]);

            $senderId = Auth::id();
            $receiverId = $request->receiver_id;

            // Find or create chat
            $chat = \App\Models\Chat::where(function($query) use ($senderId, $receiverId) {
                $query->where(function($q) use ($senderId, $receiverId) {
                    $q->where('user1_id', $senderId)
                        ->where('user2_id', $receiverId);
                })->orWhere(function($q) use ($senderId, $receiverId) {
                    $q->where('user1_id', $receiverId)
                        ->where('user2_id', $senderId);
                });
            })->first();

            if (!$chat) {
                $chat = \App\Models\Chat::create([
                    'user1_id' => $senderId,
                    'user2_id' => $receiverId
                ]);
            }

            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $request->message
            ]);

            // Broadcast event
            event(new MessageSent($message));

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => $message->load(['sender', 'receiver'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete message for the authenticated user only
     */
    public function deleteMessageForMe($messageId)
    {
        try {
            $userId = Auth::id();
            $message = Message::where('id', $messageId)
                ->where(function($query) use ($userId) {
                    $query->where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId);
                })
                ->firstOrFail();

            if ($message->sender_id == $userId) {
                $message->update(['deleted_for_sender' => true]);
            } else {
                $message->update(['deleted_for_receiver' => true]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Message deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete message for both sender and receiver
     */
    public function deleteMessageForBoth($messageId)
    {
        try {
            $userId = Auth::id();
            $message = Message::where('id', $messageId)
                ->where('sender_id', $userId)
                ->firstOrFail();

            $message->update([
                'deleted_for_sender' => true,
                'deleted_for_receiver' => true
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Message deleted for both users successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Host-only: Send a pre-approval to a guest
     */
    public function sendPreApproval(Request $request)
    {
        if (!$request->user()->isHost()) {
            return response()->json(['status' => 'error', 'message' => 'Only hosts can send pre-approvals.'], 403);
        }
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
            'meta' => 'nullable|array', // e.g., booking_id, offer details
        ]);
        $chat = \App\Models\Chat::findOrFail($request->chat_id);
        // Ensure the host is a participant
        if ($chat->user1_id !== $request->user()->id && $chat->user2_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'You are not a participant in this chat.'], 403);
        }
        $guestId = $chat->user1_id === $request->user()->id ? $chat->user2_id : $chat->user1_id;
        $msg = \App\Models\Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $guestId,
            'message' => $request->message,
            'type' => 'pre_approval',
            'meta' => $request->meta ?? [],
        ]);
        event(new MessageSent($msg));
        return response()->json(['status' => 'success', 'message' => 'Pre-approval sent.', 'data' => $msg]);
    }

    /**
     * Host-only: Send a special offer to a guest
     */
    public function sendSpecialOffer(Request $request)
    {
        if (!$request->user()->isHost()) {
            return response()->json(['status' => 'error', 'message' => 'Only hosts can send special offers.'], 403);
        }
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
            'meta' => 'nullable|array', // e.g., offer details
        ]);
        $chat = \App\Models\Chat::findOrFail($request->chat_id);
        // Ensure the host is a participant
        if ($chat->user1_id !== $request->user()->id && $chat->user2_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'You are not a participant in this chat.'], 403);
        }
        $guestId = $chat->user1_id === $request->user()->id ? $chat->user2_id : $chat->user1_id;
        $msg = \App\Models\Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $guestId,
            'message' => $request->message,
            'type' => 'special_offer',
            'meta' => $request->meta ?? [],
        ]);
        event(new MessageSent($msg));
        return response()->json(['status' => 'success', 'message' => 'Special offer sent.', 'data' => $msg]);
    }

    /**
     * Host-only: Get saved replies
     */
    public function getSavedReplies(Request $request)
    {
        if (!$request->user()->isHost()) {
            return response()->json(['status' => 'error', 'message' => 'Only hosts can view saved replies.'], 403);
        }
        $replies = \App\Models\SavedReply::where('host_id', $request->user()->id)->get();
        return response()->json(['status' => 'success', 'data' => $replies]);
    }

    /**
     * Host-only: Add a saved reply
     */
    public function addSavedReply(Request $request)
    {
        if (!$request->user()->isHost()) {
            return response()->json(['status' => 'error', 'message' => 'Only hosts can add saved replies.'], 403);
        }
        $request->validate([
            'reply_text' => 'required|string|max:1000',
        ]);
        $reply = \App\Models\SavedReply::create([
            'host_id' => $request->user()->id,
            'reply_text' => $request->reply_text,
        ]);
        return response()->json(['status' => 'success', 'message' => 'Saved reply added.', 'data' => $reply]);
    }

    /**
     * Guest-only: Start a new chat thread with a host
     */
    public function startChatThread(Request $request)
    {
        $request->validate([
            'host_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'listing_id' => 'nullable|exists:listings,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'category' => 'nullable|string',
        ]);
        $host = \App\Models\User::find($request->host_id);
        if (!$host || !$host->isHost()) {
            return response()->json(['status' => 'error', 'message' => 'Recipient must be a host.'], 422);
        }
        $user = $request->user();
        // Only guests or hosts booking a listing can start a thread
        $isBookingContext = $request->has('listing_id') || $request->has('booking_id');
        if (!$user->isGuest() && !($user->isHost() && $isBookingContext)) {
            return response()->json(['status' => 'error', 'message' => 'Only guests or hosts booking a listing can start a new chat thread.'], 403);
        }
        // Prevent guest-to-guest threads
        if ($user->isGuest() && $host->isGuest()) {
            return response()->json(['status' => 'error', 'message' => 'Thread must be between a guest and a host, or a host booking a listing.'], 422);
        }
        // Determine context and dynamic category
        $contextType = null;
        $contextId = null;
        $category = null;
        if ($request->has('listing_id')) {
            $contextType = 'listing';
            $contextId = $request->listing_id;
            $listing = \App\Models\Listing::with(['category', 'propertyType'])->find($request->listing_id);
            if ($listing && $listing->category) {
                $category = $listing->category->name;
            } elseif ($listing && $listing->propertyType) {
                $category = $listing->propertyType->name;
            }
        } elseif ($request->has('booking_id')) {
            $contextType = 'booking';
            $contextId = $request->booking_id;
            $booking = \App\Models\Booking::with(['property.category', 'property.propertyType'])->find($request->booking_id);
            if ($booking && $booking->property && $booking->property->category) {
                $category = $booking->property->category->name;
            } elseif ($booking && $booking->property && $booking->property->propertyType) {
                $category = $booking->property->propertyType->name;
            }
        }
        // Fallback: use explicit category if provided, else 'Support'
        if (!$category) {
            $category = $request->input('category', 'Support');
        }
        // Check for existing thread with same guest, host, and context
        $existingThread = \App\Models\Thread::where('guest_id', $user->id)
            ->where('host_id', $host->id)
            ->where('context_type', $contextType)
            ->where('context_id', $contextId)
            ->first();
        if ($existingThread) {
            return response()->json(['status' => 'error', 'message' => 'A thread already exists for this context.', 'thread_id' => $existingThread->id], 409);
        }
        // Create the thread
        $thread = \App\Models\Thread::create([
            'guest_id' => $user->id,
            'host_id' => $host->id,
            'thread_type' => 'inquiry',
            'context_type' => $contextType,
            'context_id' => $contextId,
            'status' => 'active',
            'category' => $category,
        ]);
        // Create the first message
        $msg = \App\Models\Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'receiver_id' => $host->id,
            'message' => $request->message,
            'type' => 'thread_start',
            'meta' => [
                'listing_id' => $request->listing_id ?? null,
                'booking_id' => $request->booking_id ?? null,
            ],
        ]);
        event(new MessageSent($msg));
        return response()->json(['status' => 'success', 'message' => 'Chat thread started.', 'thread_id' => $thread->id, 'data' => $msg]);
    }

    /**
     * Send a new message (thread-based)
     */
    public function sendThreadMessage(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:threads,id',
            'message' => 'required|string',
        ]);
        $thread = \App\Models\Thread::findOrFail($request->thread_id);
        $userId = $request->user()->id;
        // Only participants can send
        if ($thread->guest_id !== $userId && $thread->host_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'You are not a participant in this thread.'], 403);
        }
        $receiverId = $thread->guest_id === $userId ? $thread->host_id : $thread->guest_id;
        $msg = \App\Models\Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);
        event(new MessageSent($msg));
        return response()->json(['status' => 'success', 'message' => 'Message sent.', 'data' => $msg]);
    }

    /**
     * Get messages for a thread (with pagination)
     */
    public function getThreadMessages(Request $request, $threadId)
    {
        $userId = $request->user()->id;
        $thread = \App\Models\Thread::where('id', $threadId)
            ->where(function($q) use ($userId) {
                $q->where('guest_id', $userId)->orWhere('host_id', $userId);
            })
            ->firstOrFail();
        $perPage = $request->input('per_page', 20);
        $messages = \App\Models\Message::where('thread_id', $threadId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
        return response()->json(['status' => 'success', 'data' => $messages]);
    }

    /**
     * Broadcast typing indicator to chat participants
     */
    public function typingStatus(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'is_typing' => 'required|boolean',
        ]);
        $chat = \App\Models\Chat::findOrFail($request->chat_id);
        $userId = $request->user()->id;
        // Ensure user is a participant
        if ($chat->user1_id !== $userId && $chat->user2_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'You are not a participant in this chat.'], 403);
        }
        event(new \App\Events\Typing($chat->id, $userId, $request->is_typing));
        return response()->json(['status' => 'success', 'message' => 'Typing status broadcasted.']);
    }

    /**
     * Broadcast presence update (online/offline) to chat partners
     */
    public function presenceUpdate(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);
        $userId = $request->user()->id;
        event(new \App\Events\PresenceUpdated($userId, $request->is_online));
        return response()->json(['status' => 'success', 'message' => 'Presence status broadcasted.']);
    }

    /**
     * List all threads for the authenticated user (as guest or host)
     */
    public function getThreads(Request $request)
    {
        $userId = $request->user()->id;
        $query = \App\Models\Thread::where(function($q) use ($userId) {
            $q->where('guest_id', $userId)->orWhere('host_id', $userId);
        });
        if ($request->has('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        $threads = $query->with(['guest', 'host'])
            ->orderBy('updated_at', 'desc')
            ->paginate($request->input('per_page', 20));
        // Return category metadata with each thread
        return response()->json(['status' => 'success', 'data' => $threads]);
    }

    /**
     * Fetch a single thread by ID, with messages and metadata
     */
    public function getThreadById(Request $request, $id)
    {
        $userId = $request->user()->id;
        $thread = \App\Models\Thread::with(['guest', 'host', 'messages.sender', 'messages.receiver'])
            ->where('id', $id)
            ->where(function($q) use ($userId) {
                $q->where('guest_id', $userId)->orWhere('host_id', $userId);
            })
            ->firstOrFail();
        return response()->json(['status' => 'success', 'data' => $thread]);
    }

    /**
     * Upload a media file for chat (returns CDN/public URL, does not store message)
     * POST /api/v1/chat/upload-media
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max, adjust as needed
        ]);
        $file = $request->file('file');

        // (Optional) Virus scan placeholder
        // e.g., use ClamAV or a third-party service
        // if (!VirusScanner::scan($file->getPathname())) {
        //     return response()->json(['error' => 'File failed virus scan.'], 400);
        // }

        // Upload to cloud storage (S3/CDN)
        $path = $file->store('chat-media', 's3');
        $url = \Storage::disk('s3')->url($path); // Or use a presigned URL if needed

        return response()->json([
            'url' => $url,
            'type' => $file->getMimeType(),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }
} 