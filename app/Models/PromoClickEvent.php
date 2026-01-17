<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoClickEvent extends Model
{
    protected $fillable = [
        'hotel_id',
        'user_id',
        'session_id',
        'request_id',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
