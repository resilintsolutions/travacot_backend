<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCase extends Model
{
    protected $fillable = [
        'reservation_id',
        'hotel_id',
        'buyer_id',
        'seller_id',
        'conversation_id',
        'purchase_price',
        'currency',
        'bookings_24h',
        'status',
        'decision',
        'seller_responded',
        'buyer_responded',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'bookings_24h' => 'integer',
        'seller_responded' => 'boolean',
        'buyer_responded' => 'boolean',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
