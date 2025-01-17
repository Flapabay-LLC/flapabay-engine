<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutOption extends Model
{
    /** @use HasFactory<\Database\Factories\PayoutOptionFactory> */
    use HasFactory;
 /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'icon_alt',
        'currency',
    ];
}
