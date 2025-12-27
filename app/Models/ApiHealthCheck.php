<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiHealthCheck extends Model
{
    protected $fillable = [
        'service_name',
        'status',
        'response_time_ms',
        'error_message',
        'last_checked_at',
    ];
}
