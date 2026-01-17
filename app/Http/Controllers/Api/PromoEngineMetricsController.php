<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoClickEvent;
use App\Models\PromoImpressionEvent;
use App\Models\PromoAttributedBooking;
use App\Models\PromoDecision;
use App\Models\Reservation;
use App\Models\SearchLog;

class PromoEngineMetricsController extends Controller
{
    public function index()
    {
        $eligibleCount = PromoDecision::where('status', 'applied')->distinct('hotel_id')->count('hotel_id');
        $activePromosCount = PromoDecision::where('status', 'applied')->count();

        $avgMarginAfter = PromoDecision::where('status', 'applied')
            ->avg('final_margin');

        $avgMarginBefore = PromoDecision::where('status', 'applied')
            ->avg('original_margin');

        $impressions = PromoImpressionEvent::count();
        $clicks = PromoClickEvent::count();
        $promoBookings = PromoAttributedBooking::count();
        $totalBookings = Reservation::count();
        $nonPromoBookings = max(0, $totalBookings - $promoBookings);
        $nonPromoViews = max(1, (SearchLog::count() - $impressions));

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        $conversion = $clicks > 0 ? round(($promoBookings / $clicks) * 100, 2) : 0;
        $nonPromoConversion = round(($nonPromoBookings / $nonPromoViews) * 100, 2);

        return response()->json([
            'eligible_properties' => $eligibleCount,
            'active_promos' => $activePromosCount,
            'avg_margin_before' => round($avgMarginBefore ?? 0, 2),
            'avg_margin_after' => round($avgMarginAfter ?? 0, 2),
            'promo_impressions' => $impressions,
            'promo_clicks' => $clicks,
            'promo_ctr' => $ctr,
            'promo_conversion' => $conversion,
            'non_promo_bookings' => $nonPromoBookings,
            'non_promo_conversion' => $nonPromoConversion,
        ]);
    }
}
