<?php

namespace App\Services\PromoEngine;

class DiscountCalculator
{
    public function rangeForMode(float $marginPercent, string $mode): array
    {
        $ranges = [
            'light' => [10, 20],
            'normal' => [21, 40],
            'aggressive' => [41, 70],
        ];

        [$minPct, $maxPct] = $ranges[$mode] ?? [0, 0];

        $minDiscount = round($marginPercent * ($minPct / 100), 2);
        $maxDiscount = round($marginPercent * ($maxPct / 100), 2);

        return [$minDiscount, $maxDiscount];
    }

    public function pickDiscount(float $marginPercent, string $mode, string $strategy): float
    {
        [$minDiscount, $maxDiscount] = $this->rangeForMode($marginPercent, $mode);

        return match ($strategy) {
            'midpoint' => round(($minDiscount + $maxDiscount) / 2, 2),
            'min' => $minDiscount,
            default => $maxDiscount,
        };
    }
}
