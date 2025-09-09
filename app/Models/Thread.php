<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'user_id',
        'thread_type',
        'context_type',
        'context_id',
        'status',
        'category',
    ];

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function host()
    {
        return $this->user();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}