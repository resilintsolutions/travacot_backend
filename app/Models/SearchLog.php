<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = [
        'user_id',
        'device_type',
        'destination_country',
        'destination_city',
        'check_in',
        'check_out',
        'adults',
        'children',
        'success',
        'response_ms',
        'meta',
    ];

    protected $casts = [
        'check_in'  => 'date',
        'check_out' => 'date',
        'success'   => 'boolean',
        'meta'      => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
