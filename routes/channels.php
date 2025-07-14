<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Allow the user themselves
    if ($user->id == $userId) return true;
    // Allow chat partners (any chat where user is user1 or user2)
    return \App\Models\Chat::where(function($q) use ($user, $userId) {
        $q->where('user1_id', $user->id)->where('user2_id', $userId);
    })->orWhere(function($q) use ($user, $userId) {
        $q->where('user1_id', $userId)->where('user2_id', $user->id);
    })->exists();
}); 