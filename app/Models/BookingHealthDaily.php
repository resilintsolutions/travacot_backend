<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHealthDaily extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'total_attempts',
        'success_count',
        'failure_count',
        'refund_count',
        'avg_response_time_ms',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
