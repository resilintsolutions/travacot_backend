<?php

namespace App\Services\PromoEngine;

class SafetyValidator
{
    public function isSafe(float $marginPercent, float $discountPercent, float $minProfitAfter, float $buffer): bool
    {
        $finalMargin = round($marginPercent - $discountPercent, 2);
        $minRequired = round($minProfitAfter + $buffer, 2);

        return $finalMargin >= $minRequired;
    }

    public function finalMargin(float $marginPercent, float $discountPercent): float
    {
        return round($marginPercent - $discountPercent, 2);
    }
}
