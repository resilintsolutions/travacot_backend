<?php

use App\Models\Hotel;
use App\Models\Reservation;

it('prevents cancellation of resold reservations', function () {
    $hotel = Hotel::create([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel-1',
        'status' => 'active',
    ]);

    $reservation = Reservation::create([
        'hotel_id' => $hotel->id,
        'guest_info' => ['holder' => ['name' => 'John', 'surname' => 'Doe']],
        'total_price' => 200,
        'currency' => 'USD',
        'status' => 'confirmed',
        'is_resold' => true,
    ]);

    $this->deleteJson("/api/reservations/{$reservation->id}")
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});
