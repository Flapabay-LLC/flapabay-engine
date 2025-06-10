<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all chats for the authenticated user
     */
    public function getAllMyChats()
    {
        try {
            $userId = Auth::id();
            
            $chats = Chat::where('user1_id', $userId)
                ->orWhere('user2_id', $userId)
                ->with(['user1', 'user2', 'messages' => function($query) {
                    $query->latest()->first();
                }])
                ->get()
                ->map(function($chat) use ($userId) {
                    $otherUser = $chat->getOtherUser($userId);
                    $lastMessage = $chat->messages->first();
                    
                    return [
                        'chat_id' => $chat->id,
                        'other_user' => $otherUser,
                        'last_message' => $lastMessage,
                        'unread_count' => $chat->messages()
                            ->where('receiver_id', $userId)
                            ->where('is_read', false)
                            ->where('deleted_for_receiver', false)
                            ->count()
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Chats fetched successfully',
                'data' => $chats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all unread messages for the authenticated user
     */
    public function getAllMyUnreadMessages()
    {
        try {
            $userId = Auth::id();
            
            $messages = Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->where('deleted_for_receiver', false)
                ->with(['sender', 'chat'])
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Unread messages fetched successfully',
                'data' => $messages
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch unread messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all read messages for the authenticated user
     */
    public function getAllMyReadMessages()
    {
        try {
            $userId = Auth::id();
            
            $messages = Message::where('receiver_id', $userId)
                ->where('is_read', true)
                ->where('deleted_for_receiver', false)
                ->with(['sender', 'chat'])
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Read messages fetched successfully',
                'data' => $messages
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch read messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all messages for a specific chat
     */
    public function getAllChatMessages($chatId)
    {
        try {
            $userId = Auth::id();
            
            // Verify user is part of the chat
            $chat = Chat::where('id', $chatId)
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
            Message::where('chat_id', $chatId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

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
            $chat = Chat::where(function($query) use ($senderId, $receiverId) {
                $query->where(function($q) use ($senderId, $receiverId) {
                    $q->where('user1_id', $senderId)
                        ->where('user2_id', $receiverId);
                })->orWhere(function($q) use ($senderId, $receiverId) {
                    $q->where('user1_id', $receiverId)
                        ->where('user2_id', $senderId);
                });
            })->first();

            if (!$chat) {
                $chat = Chat::create([
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
     * Send a reply to a message thread
     */
    public function sendMessageThreadReply(Request $request)
    {
        try {
            $request->validate([
                'parent_message_id' => 'required|exists:messages,id',
                'message' => 'required|string'
            ]);

            $parentMessage = Message::findOrFail($request->parent_message_id);
            $senderId = Auth::id();
            $receiverId = $parentMessage->sender_id;

            $message = Message::create([
                'chat_id' => $parentMessage->chat_id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $request->message,
                'parent_message_id' => $parentMessage->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Reply sent successfully',
                'data' => $message->load(['sender', 'receiver', 'parentMessage'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send reply',
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
} 