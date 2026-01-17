<?php

namespace App\Services\PromoEngine;

use App\Models\PromoEngineSetting;

class PromoEngineConfig
{
    public function get(): PromoEngineSetting
    {
        return PromoEngineSetting::firstOrCreate([], [
            'engine_status' => true,
            'enabled_modes' => ['light', 'normal', 'aggressive'],
            'auto_downgrade_enabled' => true,
            'hide_promo_on_fail' => false,
            'min_margin_eligibility' => 6.0,
            'safety_buffer' => 2.0,
            'min_profit_after_promo' => 4.0,
            'discount_strategy' => 'max_safe',
            'attribution_window_minutes' => 30,
        ]);
    }
}
