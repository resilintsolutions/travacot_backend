<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoDecision extends Model
{
    protected $fillable = [
        'hotel_id',
        'mode',
        'discount_percent',
        'original_margin',
        'final_margin',
        'status',
        'reason',
        'valid_until',
        'context',
    ];

    protected $casts = [
        'discount_percent' => 'float',
        'original_margin' => 'float',
        'final_margin' => 'float',
        'valid_until' => 'datetime',
        'context' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
