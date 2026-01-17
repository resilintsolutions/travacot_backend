<?php

namespace App\Services\PromoEngine;

use App\Models\PromoClickEvent;
use App\Models\PromoImpressionEvent;
use Illuminate\Http\Request;

class PromoEventTracker
{
    public function recordImpression(Request $request, ?int $hotelId, array $context = []): PromoImpressionEvent
    {
        return PromoImpressionEvent::create([
            'hotel_id' => $hotelId,
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'request_id' => $request->header('X-Request-Id'),
            'context' => $context,
        ]);
    }

    public function recordClick(Request $request, ?int $hotelId, array $context = []): PromoClickEvent
    {
        return PromoClickEvent::create([
            'hotel_id' => $hotelId,
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'request_id' => $request->header('X-Request-Id'),
            'context' => $context,
        ]);
    }
}
