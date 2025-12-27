<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelExclusion extends Model
{
    protected $fillable = [
        'hotel_id',
        'mode',
        'reason',
        'admin_id',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
