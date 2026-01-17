<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceResale extends Model
{
    protected $fillable = [
        'reservation_id',
        'seller_id',
        'buyer_id',
        'status',
        'listed_price',
        'currency',
        'listed_at',
        'sold_at',
    ];

    protected $casts = [
        'listed_at' => 'datetime',
        'sold_at' => 'datetime',
        'listed_price' => 'float',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
