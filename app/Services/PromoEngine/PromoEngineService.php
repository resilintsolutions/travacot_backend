<?php

namespace App\Services\PromoEngine;

use Illuminate\Support\Carbon;

class PromoEngineService
{
    public function __construct(
        private PromoEngineConfig $config,
        private ModeSelector $modeSelector,
        private DiscountCalculator $discountCalculator,
        private SafetyValidator $safetyValidator,
        private DecisionLogger $decisionLogger
    ) {
    }

    public function decide(float $marginPercent, ?int $hotelId = null, array $context = []): array
    {
        $settings = $this->config->get();

        if (!$settings->engine_status) {
            return $this->decision('off', null, null, $marginPercent, $marginPercent, $hotelId, $context);
        }

        if ($marginPercent < $settings->min_margin_eligibility) {
            return $this->decision('ineligible', null, null, $marginPercent, $marginPercent, $hotelId, $context);
        }

        $modes = $this->modeSelector->orderedModes($settings->enabled_modes ?? []);

        foreach ($modes as $mode) {
            [$minDiscount, $maxDiscount] = $this->discountCalculator->rangeForMode($marginPercent, $mode);
            $selected = $this->discountCalculator->pickDiscount(
                $marginPercent,
                $mode,
                $settings->discount_strategy
            );

            if ($selected < $minDiscount) {
                $selected = $minDiscount;
            }

            if ($selected > $maxDiscount) {
                $selected = $maxDiscount;
            }

            if ($this->safetyValidator->isSafe(
                $marginPercent,
                $selected,
                $settings->min_profit_after_promo,
                $settings->safety_buffer
            )) {
                $finalMargin = $this->safetyValidator->finalMargin($marginPercent, $selected);
                return $this->decision('applied', $mode, $selected, $finalMargin, $marginPercent, $hotelId, $context);
            }

            if (!$settings->auto_downgrade_enabled) {
                break;
            }
        }

        $status = $settings->hide_promo_on_fail ? 'hidden' : 'unsafe';

        return $this->decision($status, null, null, $marginPercent, $marginPercent, $hotelId, $context);
    }

    public function applyToPrice(float $vendorNet, float $marginPercent, float $discountPercent): array
    {
        $finalMargin = $this->safetyValidator->finalMargin($marginPercent, $discountPercent);
        $finalPrice = round($vendorNet * (1 + $finalMargin / 100), 2);

        return [
            'final_price' => $finalPrice,
            'final_margin' => $finalMargin,
        ];
    }

    protected function decision(
        string $status,
        ?string $mode,
        ?float $discountPercent,
        float $finalMargin,
        float $originalMargin,
        ?int $hotelId,
        array $context
    ): array {
        $payload = [
            'hotel_id' => $hotelId,
            'status' => $status,
            'mode' => $mode,
            'discount_percent' => $discountPercent,
            'original_margin' => $originalMargin,
            'final_margin' => $finalMargin,
            'valid_until' => Carbon::now()->addMinutes(5),
            'context' => $context,
            'reason' => $status !== 'applied' ? $status : null,
        ];

        $this->decisionLogger->log($payload);

        return $payload;
    }
}
