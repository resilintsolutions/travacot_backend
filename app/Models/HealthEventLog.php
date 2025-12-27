<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthEventLog extends Model
{
    protected $table = 'health_event_logs';

    protected $fillable = [
        'event_date',
        'domain',
        'action',
        'status',
        'country',
        'destination',
        'response_time_ms',
        'meta',
    ];

    protected $casts = [
        'event_date' => 'date',
        'meta' => 'array',
        'response_time_ms' => 'integer',
    ];

    /* -------- SCOPES -------- */

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('event_date', $date);
    }

    /* ----------------------------------------------------
     | SCOPES (very useful later)
     |----------------------------------------------------*/

    public function scopeDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailure($query)
    {
        return $query->where('status', 'failure');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
