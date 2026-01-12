<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'confirmation_number',
        'hotel_id',
        'room_id',
        'guest_info',
        'total_price',
        'markup_amount',
        'currency',
        'status',
        'channel',
        'check_in',
        'check_out',
        'raw_response',
        'adults',
        'children',
        'customer_name',
        'customer_email',
        'booking_channel',
        'supplier_reference',
        'stripe_payment_intent_id',
        'payment_status',
    ];

    protected $casts = [
        'guest_info'    => 'array',
        'raw_response'  => 'array',
        'check_in'      => 'date',
        'check_out'     => 'date',
        'markup_amount' => 'float',
        'total_price'   => 'float',
    ];


    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Total revenue contribution (selling price incl. markup).
     */
    public function getRevenueAttribute(): float
    {
        return (float) $this->total_price;
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [
            'failed',
            'failed_booking',
            'payment_failed',
        ]);
    }

    public function isCancellable(): bool
    {
        $raw = $this->raw_response ?? [];

        $policies = collect(data_get($raw, 'hotel.rooms', []))
            ->flatMap(fn ($room) => $room['rates'] ?? [])
            ->flatMap(fn ($rate) => $rate['cancellationPolicies'] ?? []);

        if ($policies->isEmpty()) {
            return false;
        }

        // If ANY policy allows cancellation before now → allow
        return $policies->contains(function ($p) {
            $from = data_get($p, 'from');
            return $from && now()->lt(\Carbon\Carbon::parse($from));
        });
    }


}
