<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoEngineSetting extends Model
{
    protected $fillable = [
        'engine_status',
        'enabled_modes',
        'auto_downgrade_enabled',
        'hide_promo_on_fail',
        'min_margin_eligibility',
        'safety_buffer',
        'min_profit_after_promo',
        'discount_strategy',
        'attribution_window_minutes',
    ];

    protected $casts = [
        'engine_status' => 'boolean',
        'enabled_modes' => 'array',
        'auto_downgrade_enabled' => 'boolean',
        'hide_promo_on_fail' => 'boolean',
        'min_margin_eligibility' => 'float',
        'safety_buffer' => 'float',
        'min_profit_after_promo' => 'float',
        'attribution_window_minutes' => 'integer',
    ];
}
