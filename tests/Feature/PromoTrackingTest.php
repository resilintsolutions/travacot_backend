<?php

use App\Models\Hotel;

it('records promo impression and click events', function () {
    $hotel = Hotel::create([
        'name' => 'Promo Hotel',
        'slug' => 'promo-hotel-1',
        'status' => 'active',
    ]);

    $impression = $this->postJson('/api/promo-engine/impression', [
        'hotel_id' => $hotel->id,
        'context' => ['page' => 'search'],
    ]);

    $impression->assertOk()->assertJson(['success' => true]);

    $click = $this->postJson('/api/promo-engine/click', [
        'hotel_id' => $hotel->id,
        'context' => ['page' => 'search'],
    ]);

    $click->assertOk()->assertJson(['success' => true]);
});
