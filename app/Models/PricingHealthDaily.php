<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingHealthDaily extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'total_quotes',
        'below_msp_count',
        'missing_tax_count',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
