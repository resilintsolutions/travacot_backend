<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelbedsService;
use App\Services\MediaService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Services\HotelExclusionService;
use App\Models\HealthEventLog;
use App\Services\PromoEngine\PromoEngineService;
use App\Services\PromoEngine\PromoEventTracker;


class SearchController extends Controller
{
    public function search(
        Request $request,
        HotelbedsService $hb,
        MediaService $mediaService,
        HotelExclusionService $exclusionService,
        PromoEngineService $promoEngine,
        PromoEventTracker $promoTracker
    )
    {
        $data = $request->validate([
            'destination'           => 'required_without:hotelIds|string',
            'hotelIds'              => 'nullable|string',
            'checkIn'               => 'required|date',
            'checkOut'              => 'required|date|after:checkIn',
            'guests.adults'         => 'required|integer|min:1',
            'guests.children'       => 'nullable|integer|min:0',
            'guests.childrenAges.*' => 'integer|min:0|max:17',
            'currency'              => 'nullable|string|size:3',

            'returnDailyRate'       => 'nullable|boolean',
        ]);

        // --- Pax building ---
        $adults   = (int) data_get($data, 'guests.adults', 2);
        $children = (int) data_get($data, 'guests.children', 0);
        $ages     = data_get($data, 'guests.childrenAges', []);

        $paxes = [];
        for ($i = 0; $i < $adults; $i++) {
            $paxes[] = ['type' => 'AD', 'age' => 30];
        }
        for ($i = 0; $i < $children; $i++) {
            $paxes[] = ['type' => 'CH', 'age' => $ages[$i] ?? 8];
        }

        // --- Hotelbeds payload ---
        $payload = [
            'stay' => [
                'checkIn'  => $data['checkIn'],
                'checkOut' => $data['checkOut'],
            ],
            'occupancies' => [[
                'rooms'    => 1,
                'adults'   => $adults,
                'children' => $children,
                'paxes'    => $paxes,
            ]],
        ];

        if (!empty($data['hotelIds'])) {
            $payload['hotels'] = [
                'hotel' => array_map('trim', explode(',', $data['hotelIds'])),
            ];
        } else {
            $payload['destination'] = [
                'code' => $data['destination'],
            ];
        }

        if ($request->boolean('returnDailyRate')) {
            $payload['dailyRate'] = true;
        }

        // --- Call Hotelbeds availability ---
        $resp = $hb->availability($payload);
        $start = microtime(true);
        $resp  = $hb->availability($payload);
        $ms    = (int) ((microtime(true) - $start) * 1000);

        $hotels = data_get($resp, 'hotels.hotels', []);

        HealthEventLog::create([
            'event_date' => now()->toDateString(),
            'domain' => 'availability',
            'action' => 'search',
            'status' => isset($resp['error'])
                ? 'failure'
                : (count($hotels) > 0 ? 'success' : 'failure'),

            'country' => $data['destination'] ?? null,
            'destination' => $data['destination'] ?? null,
            'response_time_ms' => $ms,

            'meta' => [
                'hotel_count' => count($hotels),
                'supplier' => 'hotelbeds',
                'error' => $resp['error']['message'] ?? null,
            ],
        ]);

        if (isset($resp['error'])) {
            return response()->json([
                'success' => false,
                'error' => $resp['error'],
            ], 400);
        }

        $results = [];

        foreach ($hotels as $h) {
            $vendorCode = $h['code'] ?? null;

            // 1) Find cheapest room / rate
            $selectedRoom = null;
            $selectedRate = null;
            $bestNet      = null;

            foreach (data_get($h, 'rooms', []) as $room) {
                foreach (data_get($room, 'rates', []) as $rate) {
                    $net = (float) data_get($rate, 'net', 0);
                    if ($net <= 0) continue;

                    if ($bestNet === null || $net < $bestNet) {
                        $bestNet      = $net;
                        $selectedRoom = $room;
                        $selectedRate = $rate;
                    }
                }
            }

            if (!$selectedRate) continue;

            // 2) Content lookup
            $content = $vendorCode ? $hb->getHotelContent($vendorCode) : [];
            $contentHotel = data_get($content, 'hotel', []);

            $countryCode = data_get($contentHotel, 'country.isoCode') ?? data_get($h, 'countryCode');
            $cityCode    = data_get($contentHotel, 'destination.code') ?? data_get($h, 'destinationCode');

            // 3) PRICING LOGIC
            $lowestNet  = (float) data_get($h, 'minRate');
            $highestNet = (float) data_get($h, 'maxRate');
            $currency   = data_get($h, 'currency') ?? data_get($resp, 'hotels.currency');

            $pricingContext = [
                'bookings_24h' => (int) data_get($h, 'bookings24h', 0),
                'market_rate' => null,
                'competitor_rate' => null,
                'conversion_rate_percent' => null,
            ];

            $lowestPricing = PricingService::calculatePriceForLocation(
                $lowestNet,
                null,
                $countryCode,
                $cityCode,
                $pricingContext
            );

            $highestPricing = PricingService::calculatePriceForLocation(
                $highestNet,
                null,
                $countryCode,
                $cityCode,
                $pricingContext
            );

            // 4) Metadata & Labeling
            $categoryCode = $h['categoryCode'] ?? null;
            $rating       = $categoryCode ? (int) preg_replace('/\D/', '', $categoryCode) : null;

            $contentCountryName = data_get($contentHotel, 'country.description.content', '');
            $contentCityName    = data_get($contentHotel, 'city.content', '');
            $destinationName    = data_get($contentHotel, 'destination.name.content', '');
            $locationAddress    = data_get($contentHotel, 'address.content', '');
            $fullAddress        = trim($contentCountryName . ', ' . $contentCityName . ', ' . $locationAddress, ', ');

            // “Only X left at this price!”
            $allotment = data_get($selectedRate, 'allotment');
            $roomsLeftLabel = null;
            if (is_numeric($allotment) && (int)$allotment <= 5 && (int)$allotment > 0) {
                $roomsLeftLabel = "Only {$allotment} left at this price on our site!";
            }

            // 5) ✅ TAX LOGIC (FIXED, SAFE, FRONTEND-COMPATIBLE)
            $taxes       = [];
            $taxesTotal  = 0.0;

            // Case 1: Explicit tax objects (PMI, etc.)
            $rateTaxes = data_get($selectedRate, 'taxes.taxes', []);
            if (!empty($rateTaxes)) {
                foreach ($rateTaxes as $t) {
                    $taxes[] = $t;
                    if (!empty($t['amount']) && (($t['included'] ?? true) === false)) {
                        $taxesTotal += (float) $t['amount'];
                    }
                }
            }

            // Case 2: taxes flag only
            elseif (isset($selectedRate['taxes']['allIncluded'])) {
                $taxes[] = [
                    'type'      => 'info',
                    'included'  => $selectedRate['taxes']['allIncluded'],
                    'message'   => $selectedRate['taxes']['allIncluded']
                        ? 'All taxes are included'
                        : 'Local taxes may apply and are payable at the hotel',
                ];
            }

            // Case 3: rate comments (BEY, Middle East)
            elseif (!empty($selectedRate['rateCommentsId'])) {
                $comment = $hb->getRateComments($selectedRate['rateCommentsId']);
                $taxes[] = [
                    'type'    => 'comment',
                    'message' => data_get($comment, 'description')
                        ?? 'Local taxes may apply and are payable at the hotel',
                ];
            }

            if (empty($taxes)) {
                $taxes[] = [
                    'type'    => 'info',
                    'message' => 'Local taxes, if applicable, are payable at the hotel',
                ];
            }

            // 6) Cancellation
            $cancellationPolicies = data_get($selectedRate, 'cancellationPolicies', []);
            $freeCancellation = false;
            $freeCancellationUntil = null;

            if (!empty($cancellationPolicies) && is_array($cancellationPolicies)) {
                usort($cancellationPolicies, fn($a, $b) => strtotime($a['from']) <=> strtotime($b['from']));
                $firstPolicy = $cancellationPolicies[0];
                if (isset($firstPolicy['amount']) && (float)$firstPolicy['amount'] === 0.0) {
                    $freeCancellation = true;
                    $freeCancellationUntil = $firstPolicy['from'];
                }
            }

            // 7) Images
            $imageUrls = [];
            if ($vendorCode) {
                $hotelModel = Hotel::with('media')
                    ->where('vendor', 'hotelbeds')
                    ->where('vendor_id', $vendorCode)
                    ->first();

                if ($hotelModel && $hotelModel->media->count() > 0) {
                    $imageUrls = $hotelModel->media
                        ->where('collection', 'images')
                        ->take(5)
                        ->pluck('url')
                        ->toArray();
                }
            }

            try {
                $nights = Carbon::parse($data['checkIn'])
                    ->diffInDays(Carbon::parse($data['checkOut'])) ?: 1;
            } catch (\Throwable $e) {
                $nights = 1;
            }

            // Build normalized hotel data
            $hotelData = [
                'status'        => 'active', // Hotelbeds does not give inactive usually
                'description'   => data_get($contentHotel, 'description.content'),
                'images'        => $imageUrls,
                'rating'        => $rating,
                'totalReviews'  => 1,
            ];

            // Evaluate exclusion
            $evaluation = $exclusionService->evaluateFromArray($hotelData);

            // ❌ EXCLUDED → SKIP HOTEL
            if (!$evaluation['visible']) {
                continue;
            }

            // 8) Promo engine (Ongoing Deals)
            $promoDecision = $promoEngine->decide(
                $lowestPricing['margin_percent'] ?? 0,
                $hotelModel?->id,
                ['source' => 'search', 'hotel_code' => $vendorCode]
            );

            $promoPrice = null;
            if ($promoDecision['status'] === 'applied') {
                $promoPrice = $promoEngine->applyToPrice(
                    $lowestNet,
                    $lowestPricing['margin_percent'] ?? 0,
                    $promoDecision['discount_percent'] ?? 0
                );

                if ($hotelModel?->id) {
                    $impressed = $request->session()->get('promo_impressions', []);
                    if (!in_array($hotelModel->id, $impressed, true)) {
                        $promoTracker->recordImpression($request, $hotelModel->id, [
                            'source' => 'search',
                            'mode' => $promoDecision['mode'] ?? null,
                        ]);
                        $impressed[] = $hotelModel->id;
                        $request->session()->put('promo_impressions', $impressed);
                    }
                }
            }

            // 9) RESPONSE
            $results[] = [
                'code'            => $vendorCode,
                'name'            => data_get($h, 'name.content') ?? data_get($h, 'name'),
                'address'         => $fullAddress,
                'countryName'     => $contentCountryName,
                'cityName'        => $contentCityName,
                'location'        => $locationAddress,
                'countryCode'     => $countryCode,
                'cityCode'        => $cityCode,
                'destinationName' => $destinationName,
                'longitude'       => data_get($contentHotel, 'coordinates.longitude') ?? data_get($h, 'longitude'),
                'latitude'        => data_get($contentHotel, 'coordinates.latitude') ?? data_get($h, 'latitude'),
                'description'     => data_get($contentHotel, 'description.content') ?? data_get($h, 'description'),

                'category'        => $h['categoryName'] ?? null,
                'rating'          => $rating,

                'lowestRateNet'   => $lowestNet,
                'lowestRate'      => $lowestPricing['final_price'] ?? $lowestNet,
                'highestRateNet'  => $highestNet,
                'highestRate'     => $highestPricing['final_price'] ?? $highestNet,
                'currency'        => $currency,
                'marginPercent'   => $lowestPricing['margin_percent'] ?? null,
                'marginSource'    => $lowestPricing['margin_source'] ?? null,
                'msp'             => $lowestPricing['effective_min'] ?? null,

                'roomName'              => data_get($selectedRoom, 'name'),
                'boardName'             => data_get($selectedRate, 'boardName'),
                'paymentType'           => data_get($selectedRate, 'paymentType'),
                'rateType'              => data_get($selectedRate, 'rateType'),
                'allotment'             => $allotment,
                'roomsLeftLabel'        => $roomsLeftLabel,
                'freeCancellation'      => $freeCancellation,
                'freeCancellationUntil' => $freeCancellationUntil,
                'noPrepaymentNeeded'    => (data_get($selectedRate, 'paymentType') === 'AT_HOTEL'),

                // ✅ SAME KEYS, SAFER DATA
                'taxes'       => $taxes,
                'taxesTotal'  => $taxesTotal,
                'nights'      => $nights,
                'adults'      => $adults,
                'children'    => $children,

                'images'          => $imageUrls,
                'recommended'     => false,
                'totalReviews'    => 1,
                'promo' => [
                    'status' => $promoDecision['status'] ?? 'none',
                    'mode' => $promoDecision['mode'] ?? null,
                    'discount_percent' => $promoDecision['discount_percent'] ?? null,
                    'final_margin' => $promoDecision['final_margin'] ?? null,
                    'promo_price' => $promoPrice['final_price'] ?? null,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function countries(HotelbedsService $hb)
    {
        $resp = $hb->getHotelbedsCountries();

        return response()->json([
            'success'   => $resp['success'],
            'countries' => $resp['data'],
        ]);
    }

    public function destinations(Request $request, HotelbedsService $hb)
    {
        $request->validate([
            'country' => 'required|string|size:2',
        ]);

        $resp = $hb->getHotelbedsDestinations([
            'countryCode' => strtoupper($request->query('country')),
        ]);

        return response()->json([
            'success'      => $resp['success'],
            'destinations' => $resp['data'],
        ]);
    }
}
