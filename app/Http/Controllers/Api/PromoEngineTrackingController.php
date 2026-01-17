<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoTrackingRequest;
use App\Services\PromoEngine\PromoEventTracker;

class PromoEngineTrackingController extends Controller
{
    public function impression(PromoTrackingRequest $request, PromoEventTracker $tracker)
    {
        $event = $tracker->recordImpression(
            $request,
            $request->input('hotel_id'),
            $request->input('context', [])
        );

        return response()->json(['success' => true, 'event_id' => $event->id]);
    }

    public function click(PromoTrackingRequest $request, PromoEventTracker $tracker)
    {
        $event = $tracker->recordClick(
            $request,
            $request->input('hotel_id'),
            $request->input('context', [])
        );

        return response()->json(['success' => true, 'event_id' => $event->id]);
    }
}
