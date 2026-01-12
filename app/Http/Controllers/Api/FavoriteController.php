<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Hotel;
use App\Services\MediaService;
use App\Services\PricingService;
use App\Services\HotelbedsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /**
     * List favorites (Hotels)
     */
public function index(Request $request, HotelbedsService $hb)
{
    $userId = $request->user()->id ?? 1;

    // Default values for favorites
    $adults   = 2;
    $children = 0;
    $checkIn  = now()->addDays(30)->format('Y-m-d');
    $checkOut = now()->addDays(31)->format('Y-m-d');

    $hotelIds = Favorite::where('user_id', $userId)
        ->where('item_type', Hotel::class)
        ->pluck('item_id')
        ->toArray();

    if (empty($hotelIds)) {
        return response()->json([
            'success' => true,
            'results' => [],
        ]);
    }

    $payload = [
        'stay' => [
            'checkIn'  => $checkIn,
            'checkOut' => $checkOut,
        ],
        'occupancies' => [[
            'rooms'    => 1,
            'adults'   => $adults,
            'children' => $children,
        ]],
        'hotels' => [
            'hotel' => array_map('strval', $hotelIds),
        ],
    ];

    $resp = $hb->availability($payload);

    if (!empty($resp['error'])) {
        return response()->json([
            'success' => false,
            'error'   => $resp['error'],
        ], 400);
    }

    $hotels  = data_get($resp, 'hotels.hotels', []);
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

            // 2) Content lookup
            $content = $vendorCode ? $hb->getHotelContent($vendorCode) : [];
            $contentHotel = data_get($content, 'hotel', []);

            $countryCode = data_get($contentHotel, 'country.isoCode') ?? data_get($h, 'countryCode');
            $cityCode    = data_get($contentHotel, 'destination.code') ?? data_get($h, 'destinationCode');

            // 3) PRICING LOGIC
            $lowestNet  = (float) data_get($h, 'minRate');
            $highestNet = (float) data_get($h, 'maxRate');
            $currency   = data_get($h, 'currency') ?? data_get($resp, 'hotels.currency');

            // Match RoomSelectionController context
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

            // 5) Cancellation & Tax Logic
            $taxes = data_get($selectedRate, 'taxes.taxes', []) ?: data_get($selectedRate, 'taxes', []);
            $taxesTotal = 0.0;
            foreach ($taxes as $t) {
                $included = $t['included'] ?? null;
                // Add to total if explicitly not included
                if ($included === false || $included === 'false' || $included === 0 || $included === null) {
                    $taxesTotal += (float) ($t['amount'] ?? 0);
                }
            }

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

            // 6) Images
            $imageUrls = [];
            if ($vendorCode) {
                $hotelModel = Hotel::with('media')->where('vendor', 'hotelbeds')->where('vendor_id', $vendorCode)->first();
                if ($hotelModel && $hotelModel->media->count() > 0) {
                    $imageUrls = $hotelModel->media->where('collection', 'images')->take(5)->pluck('url')->toArray();
                } else {
                    $images = data_get($contentHotel, 'images', []) ?: data_get($h, 'images', []);
                    foreach (array_slice($images, 0, 5) as $img) {
                        $raw = $img['path'] ?? $img['url'] ?? null;
                        if ($raw) {
                            $imageUrls[] = preg_match('#^https?://#i', $raw) ? $raw : 'https://photos.hotelbeds.com/giata/' . ltrim($raw, '/');
                        }
                    }
                }
            }

            try {
                $nights = Carbon::parse($data['checkIn'])->diffInDays(Carbon::parse($data['checkOut'])) ?: 1;
            } catch (\Throwable $e) { $nights = 1; }

            // 7) Response Object
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

                // 💰 PRICING
                'lowestRateNet'   => $lowestNet,
                'lowestRate'      => $lowestPricing['final_price'] ?? $lowestNet,
                'highestRateNet'  => $highestNet,
                'highestRate'     => $highestPricing['final_price'] ?? $highestNet,
                'currency'        => $currency,
                'marginPercent'   => $lowestPricing['margin_percent'] ?? null,
                'marginSource'    => $lowestPricing['margin_source'] ?? null,
                'msp'             => $lowestPricing['effective_min'] ?? null,

                // 🛏️ Room / Rate Details
                'roomName'              => data_get($selectedRoom, 'name'),
                'boardName'             => data_get($selectedRate, 'boardName'),
                'paymentType'           => data_get($selectedRate, 'paymentType'),
                'rateType'              => data_get($selectedRate, 'rateType'),
                'allotment'             => $allotment,
                'roomsLeftLabel'        => $roomsLeftLabel,
                'freeCancellation'      => $freeCancellation,
                'freeCancellationUntil' => $freeCancellationUntil,
                'noPrepaymentNeeded'    => (data_get($selectedRate, 'paymentType') === 'AT_HOTEL'),

                // 🧾 Taxes & Guests
                'taxes'           => $taxes,
                'taxesTotal'      => $taxesTotal,
                'nights'          => $nights,
                'adults'          => $adults,
                'children'        => $children,

                'images'          => $imageUrls,
                'recommended'     => false,
                'totalReviews'    => 0,
                'isFavorite'  => true,
            ];
        }

    return response()->json([
        'success' => true,
        'results' => $results,
    ]);
}


    /**
     * Add to favorites
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'itemType' => 'required|string|in:hotel',
                'itemId'   => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        }

        $favorite = Favorite::firstOrCreate([
            'user_id'   => $request->user()->id,
            'item_type' => Hotel::class,
            'item_id'   => $data['itemId'], // Hotelbeds code
        ]);

        return response()->json([
            'success'  => true,
            'favorite' => $favorite,
        ], 201);
    }

    /**
     * Remove from favorites
     */
    public function destroy(Favorite $favorite, Request $request)
    {
        abort_unless($favorite->user_id === $request->user()->id, 403);

        $favorite->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Check if item is favorite
     */
    public function check(Request $request)
    {
        try {
            $data = $request->validate([
                'itemType' => 'required|string|in:hotel',
                'itemId'   => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'exists' => false,
            ], 422);
        }

        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('item_type', Hotel::class)
            ->where('item_id', $data['itemId'])
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
