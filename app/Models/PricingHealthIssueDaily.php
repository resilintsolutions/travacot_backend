<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingHealthIssueDaily extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'issue',
        'total',
        'price_changed_count',
        'missing_price_count',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
