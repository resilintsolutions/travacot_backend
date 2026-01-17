<?php

use App\Models\Reservation;

it('marks resold reservations as not cancellable', function () {
    $reservation = new Reservation([
        'is_resold' => true,
        'raw_response' => [],
    ]);

    expect($reservation->isCancellable())->toBeFalse();
});
