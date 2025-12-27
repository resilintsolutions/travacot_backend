<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityHealthCountryDaily extends Model
{
    protected $table = 'availability_health_country_daily';

    protected $fillable = [
        'date',
        'supplier',
        'country',
        'total_requests',
        'success_count',
        'failure_count',
        'timeout_count',
        'avg_response_time_ms',
        'no_rooms_returned_pct',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
