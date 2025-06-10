<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_staff_response'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_staff_response' => 'boolean'
    ];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 