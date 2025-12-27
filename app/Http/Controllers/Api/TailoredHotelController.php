<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HotelbedsService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TailoredHotelController extends Controller
{
    protected HotelbedsService $hb;

    public function __construct(HotelbedsService $hb)
    {
        $this->hb = $hb;
    }

    /**
     * GET /api/tailored-hotels
     */
    public function index(Request $request)
    {
        $nights = max((int) $request->query('nights', 3), 1);
        $limit  = max((int) $request->query('limit', 40), 1);

        $availability = Cache::remember(
            "tailored:availability:n{$nights}:l{$limit}",
            30,
            fn () => $this->callAvailability($nights, $limit, $request)
        );

        return response()->json([
            'success' => true,
            'tabs'    => $this->buildTabs($availability, $nights),
            'meta'    => compact('nights', 'limit'),
        ]);
    }

    /**
     * ---------------------------
     * HOTELBEDS AVAILABILITY
     * ---------------------------
     */
    protected function callAvailability(int $nights, int $limit, Request $request): array
    {
        $checkIn  = Carbon::now()->addWeeks(2);
        $checkOut = (clone $checkIn)->addDays($nights);

        $geo = $this->resolveLocationFromIp($request);

        return $this->hb->availability([
            'stay' => [
                'checkIn'  => $checkIn->format('Y-m-d'),
                'checkOut' => $checkOut->format('Y-m-d'),
            ],
            'occupancies' => [
                ['rooms' => 1, 'adults' => 2, 'children' => 0],
            ],
            'pagination' => [
                'itemsPerPage' => $limit,
                'page' => 1,
            ],
            'language' => 'ENG',
            'geolocation' => [
                'latitude'  => $geo['lat'],
                'longitude' => $geo['lon'],
                'radius'    => 50,
                'unit'      => 'km',
            ],
        ]);
    }

    /**
     * ---------------------------
     * BUILD TABS
     * ---------------------------
     */
    protected function buildTabs(array $search, int $nights): array
    {
        $hotels = collect(data_get($search, 'hotels.hotels', []));

        if ($hotels->isEmpty()) {
            return [
                'offers'        => [],
                'weekend_deals' => [],
                'top_rated'     => [],
                'promotions'    => [],
            ];
        }

        $offersPool   = $hotels->filter(fn ($h) => $this->hasOffer($h));
        $weekendPool  = $hotels->filter(fn ($h) =>
            (int) data_get($h, 'rooms.0.rates.0.allotment', 99) <= 3
        );
        $topRatedPool = $this->buildTopRatedPool($hotels);
        $promoPool    = $offersPool;

        // fallback so frontend never gets empty sections
        $fallback = $hotels->take(20);

        return [
            'offers'        => $this->format($offersPool->isNotEmpty() ? $offersPool : $fallback, $nights),
            'weekend_deals' => $this->format($weekendPool->isNotEmpty() ? $weekendPool : $fallback, $nights),
            'top_rated'     => $this->format($topRatedPool->isNotEmpty() ? $topRatedPool : $fallback, $nights),
            'promotions'    => $this->format($promoPool->isNotEmpty() ? $promoPool : $fallback, $nights),
        ];
    }


    /**
     * ---------------------------
     * OFFER DETECTION (FIXED)
     * ---------------------------
     */
    protected function hasOffer(array $hotel): bool
    {
        foreach (data_get($hotel, 'rooms', []) as $room) {
            foreach (data_get($room, 'rates', []) as $rate) {
                if (!empty($rate['offers'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * ---------------------------
     * FORMAT + PRICING BREAKDOWN
     * ---------------------------
     */
    protected function format(Collection $hotels, int $nights): array
    {
        $out = [];

        foreach ($hotels->take(20) as $hotel) {

            $code = data_get($hotel, 'code');

            $content = Cache::remember(
                "hb:content:$code",
                3600,
                fn () => $this->hb->getHotelContent($code)
            );

            /** -------------------------
             * RATE SELECTION
             * ------------------------- */
            $rate = collect(data_get($hotel, 'rooms.0.rates', []))
                ->sortBy('net')
                ->first();

            if (!$rate) {
                continue;
            }

            $netTotal = (float) $rate['net'];
            $vendorPerNight = round($netTotal / $nights, 2);

            /** -------------------------
             * DISCOUNT CALCULATION
             * ------------------------- */
            $offerDiscount = collect($rate['offers'] ?? [])
                ->sum(fn ($o) => abs((float) $o['amount']));

            $originalTotal = $netTotal + $offerDiscount;

            $originalPerNight = round($originalTotal / $nights, 2);
            $discountPerNight = round($offerDiscount / $nights, 2);

            $discountPercent = $originalTotal > 0
                ? round(($offerDiscount / $originalTotal) * 100, 2)
                : 0;

            /** -------------------------
             * APPLY YOUR PRICING ENGINE
             * ------------------------- */
            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: $vendorPerNight,
                hotelMargin: null,
                country: data_get($content, 'hotel.country.isoCode'),
                city: data_get($content, 'hotel.city.content'),
                context: []
            );

            $finalPerNight = round($pricing['selling_price'], 2);
            $markupPercent = $vendorPerNight > 0
                ? round((($finalPerNight - $vendorPerNight) / $vendorPerNight) * 100, 2)
                : 0;
            $out[] = [
                'hotel_code' => $code,
                'name'       => data_get($hotel, 'name'),
                'city'       => data_get($content, 'hotel.city.content'),
                'country'    => data_get($content, 'hotel.country.description.content'),
                'image_url'  => $this->image($content),
                'rating'     => (int) data_get($hotel, 'rating', 0),
                'categoryCode' => data_get($hotel, 'categoryCode'),
                'nights'     => $nights,

                'pricing' => [
                    'currency' => data_get($hotel, 'currency', 'EUR'),

                    'vendor_per_night' => $originalPerNight,
                    'vendor_total'     => round($originalPerNight * $nights, 2),

                    'marked_per_night' => $vendorPerNight,
                    'marked_total'     => round($vendorPerNight * $nights, 2),

                    'final_per_night'  => $finalPerNight,
                    'final_total'      => round($finalPerNight * $nights, 2),

                    'discount_per_night' => $discountPerNight,
                    'discount_total'     => round($discountPerNight * $nights, 2),
                    'discount_percent'   => $discountPercent,

                    'markup_percent'     => $markupPercent,
                ],

                'urgency_label' => data_get($hotel, 'roomsLeftLabel'),
                'allotment'     => (int) data_get($rate, 'allotment', 0),
            ];
        }

        return $out;
    }

    protected function image(array $content): ?string
    {
        $path = data_get($content, 'hotel.images.0.path');
        return $path ? "https://photos.hotelbeds.com/giata/bigger/{$path}" : null;
    }

    protected function resolveLocationFromIp(Request $request): array
    {
        if (in_array($request->ip(), ['127.0.0.1', '::1'])) {
            return [
                'lat' => config('services.hotelbeds.geo_lat'),
                'lon' => config('services.hotelbeds.geo_lon'),
            ];
        }

        try {
            $resp = Http::timeout(2)->get("https://ipapi.co/{$request->ip()}/json/");
            if ($resp->successful()) {
                return [
                    'lat' => (float) $resp->json('latitude'),
                    'lon' => (float) $resp->json('longitude'),
                ];
            }
        } catch (\Throwable $e) {}

        return [
            'lat' => config('services.hotelbeds.geo_lat'),
            'lon' => config('services.hotelbeds.geo_lon'),
        ];
    }

    protected function buildTopRatedPool(Collection $hotels): Collection
    {
        return $hotels->filter(function ($h) {
            if ((int) data_get($h, 'rating', 0) >= 4) {
                return true;
            }

            $cat = (string) data_get($h, 'categoryCode', '');
            return str_contains($cat, '4') || str_contains($cat, '5');
        });
    }

}
