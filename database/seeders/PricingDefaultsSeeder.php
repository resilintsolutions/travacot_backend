<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MspSetting;
use App\Models\MarginRulesSetting;
use Illuminate\Support\Facades\Schema;

class PricingDefaultsSeeder extends Seeder
{
    public function run()
    {
        // Global MSP default
        MspSetting::updateOrCreate(
            ['scope' => 'global', 'country' => null, 'city' => null],
            ['msp_amount' => 45.00, 'currency' => 'USD']
        );

        // Global margin rules default
        $marginData = [
            'default_margin_percent' => 10.00,
            'min_margin_percent'     => 5.00,
            'max_margin_percent'     => 25.00,
        ];

        if (Schema::hasColumn('margin_rules_settings', 'demand_high_threshold_rooms')) {
            $marginData = array_merge($marginData, [
                'demand_high_threshold_rooms'             => 4,
                'demand_high_margin_increase_percent'     => 5.00,
                'demand_low_margin_decrease_percent'      => 5.00,
                'competitor_price_diff_threshold_percent' => 5.00,
                'competitor_margin_decrease_percent'      => -3.00,
                'conversion_threshold_percent'            => 1.20,
                'conversion_margin_decrease_percent'      => -2.00,
            ]);
        }

        MarginRulesSetting::updateOrCreate(
            ['scope' => 'global', 'country' => null, 'city' => null],
            $marginData
        );
    }
}
