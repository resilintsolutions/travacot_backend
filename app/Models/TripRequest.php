<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRequest extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'external_booking_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
