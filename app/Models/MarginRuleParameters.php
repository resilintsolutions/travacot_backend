<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarginRuleParameters extends Model
{
    protected $fillable = [
        'enable_demand_rule',
        'demand_high_threshold_rooms',
        'demand_high_margin_increase_percent',
        'demand_low_margin_decrease_percent',

        'enable_competitor_rule',
        'competitor_price_diff_threshold_percent',
        'competitor_margin_decrease_percent',

        'enable_conversion_rule',
        'conversion_threshold_percent',
        'conversion_margin_decrease_percent',
    ];
}
