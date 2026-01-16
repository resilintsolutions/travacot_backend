<?php

use App\Models\Hotel;
use App\Models\PromoClickEvent;
use App\Models\Reservation;
use App\Services\PromoEngine\PromoAttributionService;

it('attributes booking to recent promo click', function () {
    $hotel = Hotel::create([
        'name' => 'Promo Hotel',
        'slug' => 'promo-hotel-2',
        'status' => 'active',
    ]);

    $click = PromoClickEvent::create([
        'hotel_id' => $hotel->id,
        'session_id' => 'test-session',
        'request_id' => 'req-1',
        'context' => ['page' => 'search'],
    ]);

    $reservation = Reservation::create([
        'hotel_id' => $hotel->id,
        'guest_info' => ['holder' => ['name' => 'Test', 'surname' => 'User']],
        'total_price' => 100,
        'currency' => 'USD',
        'status' => 'confirmed',
    ]);

    $service = app(PromoAttributionService::class);
    $attrib = $service->attribute($reservation, $hotel->id, 'test-session', 'req-1');

    expect($attrib)->not()->toBeNull();
    expect($reservation->fresh()->promo_attributed)->toBeTrue();
});
