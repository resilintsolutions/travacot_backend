<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoAttributedBooking extends Model
{
    protected $fillable = [
        'reservation_id',
        'hotel_id',
        'user_id',
        'promo_click_event_id',
        'session_id',
        'request_id',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function clickEvent()
    {
        return $this->belongsTo(PromoClickEvent::class, 'promo_click_event_id');
    }
}
