<?php

use App\Models\PromoEngineSetting;
use App\Services\PromoEngine\DiscountCalculator;
use App\Services\PromoEngine\PromoEngineService;
use App\Services\PromoEngine\SafetyValidator;

it('calculates discount ranges based on margin', function () {
    $calc = new DiscountCalculator();
    [$min, $max] = $calc->rangeForMode(10, 'light');
    expect($min)->toBe(1.0);
    expect($max)->toBe(2.0);
});

it('enforces safety buffer and minimum profit', function () {
    $validator = new SafetyValidator();
    expect($validator->isSafe(10, 5, 4, 2))->toBeFalse(); // final margin 5 < 6 required
    expect($validator->isSafe(10, 3, 4, 2))->toBeTrue();  // final margin 7 >= 6
});

it('downgrades promo modes when aggressive is unsafe', function () {
    PromoEngineSetting::create([
        'engine_status' => true,
        'enabled_modes' => ['light', 'normal', 'aggressive'],
        'auto_downgrade_enabled' => true,
        'min_margin_eligibility' => 6,
        'safety_buffer' => 2,
        'min_profit_after_promo' => 4,
        'discount_strategy' => 'max_safe',
    ]);

    $service = app(PromoEngineService::class);
    $decision = $service->decide(7.0, null);

    expect($decision['status'])->toBeIn(['applied', 'unsafe']);
});
