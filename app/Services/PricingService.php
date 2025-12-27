<?php

namespace App\Services;

use App\Models\MarginRulesSetting;
use App\Models\MarginRuleParameters;
use App\Models\MspSetting;
use App\Models\HealthEventLog;

class PricingService
{
    /* ---------------------------------------------
     * Get RULE PARAMETERS
     * --------------------------------------------- */
    protected static function getParameters(): MarginRuleParameters
    {
        return MarginRuleParameters::first() ?? new MarginRuleParameters();
    }

    /* ---------------------------------------------
     * Resolve margin scope (City → Country → Global)
     * --------------------------------------------- */
    protected static function resolveMarginScope(?string $country, ?string $city): MarginRulesSetting
    {
        return MarginRulesSetting::forLocation($country, $city)
            ?? MarginRulesSetting::where('scope', 'global')->first();
    }

    /* ---------------------------------------------
     * Resolve MIN / MAX with fallback
     * --------------------------------------------- */
    protected static function resolveMinMax(
        MarginRulesSetting $scope,
        ?string $country
    ): array {
        $min = $scope->min_margin_percent;
        $max = $scope->max_margin_percent;

        if (is_null($min) || is_null($max)) {
            $global = MarginRulesSetting::where('scope', 'global')->first();

            if ($global) {
                $min ??= $global->min_margin_percent;
                $max ??= $global->max_margin_percent;
            }
        }

        return [$min, $max];
    }

    /* ---------------------------------------------
     * RULE A — Demand Based
     * --------------------------------------------- */
    protected static function applyDemandRule(float $margin, int $b24, MarginRuleParameters $p): float
    {
        if ($b24 <= 0) {
            return $margin - abs($p->demand_low_margin_decrease_percent);
        }

        if ($b24 <= $p->demand_high_threshold_rooms) {
            return $margin + abs($p->demand_high_margin_increase_percent);
        }

        return $margin;
    }

    /* ---------------------------------------------
     * RULE B — Competitor Price
     * --------------------------------------------- */
    protected static function applyCompetitorRule(
        float $margin,
        ?float $marketRate,
        ?float $competitorRate,
        MarginRuleParameters $p
    ): float {
        if (!$marketRate || !$competitorRate) return $margin;

        if ($marketRate > ($competitorRate * (1 + $p->competitor_price_diff_threshold_percent / 100))) {
            return $margin - abs($p->competitor_margin_decrease_percent);
        }

        return $margin;
    }

    /* ---------------------------------------------
     * RULE C — Conversion Rate
     * --------------------------------------------- */
    protected static function applyConversionRule(
        float $margin,
        ?float $conversion,
        MarginRuleParameters $p
    ): float {
        if (is_null($conversion)) return $margin;

        if ($conversion < $p->conversion_threshold_percent) {
            return $margin - abs($p->conversion_margin_decrease_percent);
        }

        return $margin;
    }

    /* ---------------------------------------------
     * MAIN PRICING PIPELINE
     * --------------------------------------------- */
    public static function calculatePriceForLocation(
        ?float $vendorRate,
        ?float $hotelMargin,
        ?string $country,
        ?string $city,
        array $context = []
    ): array {

        $vendorRate = round((float)($vendorRate ?? 0), 2);

        /* ======================
         * 1. Resolve scope
         * ====================== */
        $scope = self::resolveMarginScope($country, $city);

        /* ======================
         * 2. Base margin
         * ====================== */
        if ($hotelMargin && $hotelMargin > 0) {
            $margin = (float)$hotelMargin;
            $source = 'hotel';
        } else {
            $margin = (float)$scope->default_margin_percent;
            $source = $scope->scope;
        }

        /* ======================
         * 3. Resolve MIN/MAX
         * ====================== */
        [$min, $max] = self::resolveMinMax($scope, $country);

        if (!is_null($min)) $margin = max($margin, $min);
        if (!is_null($max)) $margin = min($margin, $max);

        /* ======================
         * 4. Apply Rules
         * ====================== */
        $p = self::getParameters();

        if ($p->enable_demand_rule ?? true) {
            $margin = self::applyDemandRule($margin, $context['bookings_24h'] ?? 0, $p);
        }

        if ($p->enable_competitor_rule ?? true) {
            $margin = self::applyCompetitorRule(
                $margin,
                $context['market_rate'] ?? null,
                $context['competitor_rate'] ?? null,
                $p
            );
        }

        if ($p->enable_conversion_rule ?? true) {
            $margin = self::applyConversionRule(
                $margin,
                $context['conversion_rate_percent'] ?? null,
                $p
            );
        }

        if (!is_null($min)) $margin = max($margin, $min);
        if (!is_null($max)) $margin = min($margin, $max);

        $margin = round($margin, 2);

        /* ======================
         * 5. Prices
         * ====================== */
        $selling = round($vendorRate * (1 + $margin / 100), 2);

        /* ======================
         * 6. MSP OVERRIDE (🔥 FIX)
         * ====================== */
        $msp = MspSetting::forLocation($country, $city);
        $mspValue = round((float)($msp->msp_amount ?? 0), 2);

        if ($mspValue > 0 && $selling < $mspValue) {
            // MSP overrides EVERYTHING
            $selling = $mspValue;

            // margin becomes derived (for reporting only)
            $margin = $vendorRate > 0
                ? round((($selling - $vendorRate) / $vendorRate) * 100, 2)
                : 0;
        }
        /* ======================
        * 6. Log Pricing Health Event
        * ====================== */

        $hasZeroPrice     = $selling <= 0;
        $isBelowMsp       = ($mspValue > 0 && $selling < $mspValue);
        $priceChanged     = isset($originalSellingPrice)
            && $originalSellingPrice > 0
            && $selling !== $originalSellingPrice;

        $missingTaxes     = empty($taxes) || $taxesTotal <= 0;

        HealthEventLog::create([
            'event_date' => now()->toDateString(),
            'domain' => 'pricing',
            'action' => 'quote',
            'status' => $hasZeroPrice ? 'failure' : 'success',

            'response_time_ms' => $responseTimeMs ?? null,

            'meta' => [
                'supplier'        => 'hotelbeds',

                // prices
                'vendor_net'      => (float) $vendorRate,
                'selling_price'   => (float) $selling,
                'original_price'  => (float) ($originalSellingPrice ?? 0),

                // pricing signals
                'below_msp'       => $isBelowMsp,
                'price_changed'   => $priceChanged,
                'missing_tax'     => $missingTaxes,
                'zero_rate'       => $hasZeroPrice,

                // context (VERY IMPORTANT for debugging)
                'currency'        => $currency ?? null,
                'country'         => $countryCode ?? null,
                'hotel_code'      => $hotelCode ?? null,
            ],
        ]);

        /* ======================
         * 7. Return data
         * ====================== */

        return [
            'vendor_net'     => $vendorRate,
            'margin_percent' => $margin,
            'selling_price'  => $selling, // ✅ NEVER below MSP
            'final_price'    => $selling,
            'effective_min'  => $mspValue,
            'margin_source'  => $source,
            'scope_used'     => $scope->scope,
            'msp_scope'      => $msp->scope ?? 'global',
        ];
    }
}
