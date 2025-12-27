<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityHealthDaily extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'country',

        'total_requests',
        'success_count',
        'failure_count',
        'timeout_count',

        'avg_response_time_ms',
        'p95_response_time_ms',

        'hotels_returned_avg',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
