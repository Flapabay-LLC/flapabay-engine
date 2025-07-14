<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'reply_text',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
} 