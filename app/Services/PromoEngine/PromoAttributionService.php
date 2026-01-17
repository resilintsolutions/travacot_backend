<?php

namespace App\Services\PromoEngine;

use App\Models\PromoAttributedBooking;
use App\Models\PromoClickEvent;
use App\Models\PromoEngineSetting;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

class PromoAttributionService
{
    public function attribute(Reservation $reservation, ?int $hotelId, string $sessionId, ?string $requestId, array $context = []): ?PromoAttributedBooking
    {
        $settings = PromoEngineSetting::first();
        $windowMinutes = $settings?->attribution_window_minutes ?? 30;
        $cutoff = Carbon::now()->subMinutes($windowMinutes);

        $click = PromoClickEvent::where('session_id', $sessionId)
            ->when($hotelId, fn ($q) => $q->where('hotel_id', $hotelId))
            ->where('created_at', '>=', $cutoff)
            ->latest()
            ->first();

        if (!$click) {
            return null;
        }

        $reservation->update(['promo_attributed' => true]);

        return PromoAttributedBooking::create([
            'reservation_id' => $reservation->id,
            'hotel_id' => $hotelId,
            'user_id' => $reservation->user_id,
            'promo_click_event_id' => $click->id,
            'session_id' => $sessionId,
            'request_id' => $requestId,
            'context' => $context,
        ]);
    }
}
