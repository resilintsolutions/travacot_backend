<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoEngineSettingsRequest;
use App\Models\PromoEngineSetting;
use App\Models\PromoDecision;
use App\Models\PromoImpressionEvent;
use App\Models\PromoClickEvent;
use App\Models\PromoAttributedBooking;
use App\Models\Reservation;
use App\Models\SearchLog;
use App\Services\PromoEngine\PromoEngineConfig;

class PromoEngineController extends Controller
{
    public function index(PromoEngineConfig $config)
    {
        $settings = $config->get();

        $eligibleCount = PromoDecision::where('status', 'applied')->distinct('hotel_id')->count('hotel_id');
        $activePromosCount = PromoDecision::where('status', 'applied')->count();

        $avgMarginAfter = PromoDecision::where('status', 'applied')->avg('final_margin');
        $avgMarginBefore = PromoDecision::where('status', 'applied')->avg('original_margin');

        $impressions = PromoImpressionEvent::count();
        $clicks = PromoClickEvent::count();
        $promoBookings = PromoAttributedBooking::count();
        $totalBookings = Reservation::count();
        $nonPromoBookings = max(0, $totalBookings - $promoBookings);
        $nonPromoViews = max(1, (SearchLog::count() - $impressions));

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        $conversion = $clicks > 0 ? round(($promoBookings / $clicks) * 100, 2) : 0;
        $nonPromoConversion = round(($nonPromoBookings / $nonPromoViews) * 100, 2);

        $metrics = [
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
        ];

        $exampleMargin = max(5, (float) ($settings->min_margin_eligibility ?? 6));
        $exampleMode = 'Aggressive';
        $exampleDiscount = round($exampleMargin * 0.6, 2);
        $exampleFinalMargin = round($exampleMargin - $exampleDiscount, 2);
        $exampleMinRequired = round($settings->min_profit_after_promo + $settings->safety_buffer, 2);
        $exampleBlocked = $exampleFinalMargin < $exampleMinRequired;

        return view('admin.promo_engine.index', [
            'settings' => $settings,
            'metrics' => $metrics,
            'example' => [
                'margin' => $exampleMargin,
                'mode' => $exampleMode,
                'discount' => $exampleDiscount,
                'final_margin' => $exampleFinalMargin,
                'min_required' => $exampleMinRequired,
                'blocked' => $exampleBlocked,
            ],
        ]);
    }

    public function update(PromoEngineSettingsRequest $request)
    {
        $settings = PromoEngineSetting::firstOrCreate([]);
        $settings->update($request->validated());

        return redirect()
            ->route('admin.promo-engine.index')
            ->with('success', 'Promo Engine settings updated.');
    }
}
