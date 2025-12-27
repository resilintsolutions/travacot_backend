<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelbedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\PricingService;


class RoomSelectionController extends Controller
{
    public function roomTypes($hotelId, Request $request, HotelbedsService $hb)
    {
        $data = $request->validate([
            'checkIn'  => 'required|date',
            'checkOut' => 'required|date|after:checkIn',
            'adults'   => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $adults   = (int) ($data['adults'] ?? 2);
        $children = (int) ($data['children'] ?? 0);

        // ---------------- PAXES ----------------
        $paxes = [];
        for ($i = 0; $i < $adults; $i++) {
            $paxes[] = ['type' => 'AD', 'age' => 30];
        }
        for ($i = 0; $i < $children; $i++) {
            $paxes[] = ['type' => 'CH', 'age' => 8];
        }

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
            'hotels' => [
                'hotel' => [$hotelId],
            ],
        ];

        $resp = $hb->availability($payload);

        if (isset($resp['error'])) {
            return response()->json([
                'success' => false,
                'error'   => $resp['error'],
            ], 400);
        }

        $hbHotel = data_get($resp, 'hotels.hotels.0');
        if (!$hbHotel) {
            return response()->json([
                'success' => false,
                'message' => 'No rooms returned from supplier',
            ], 404);
        }

        // ---------------- CONTENT API ----------------
        $content      = $hb->getHotelContent($hotelId);
        $contentHotel = data_get($content, 'hotel', $content);

        $contentRooms  = data_get($contentHotel, 'rooms', []);
        $contentImages = data_get($contentHotel, 'images', []);
        $facilities    = data_get($contentHotel, 'facilities', []);

        // ---------------- HOTEL META ----------------
        $hotelDesc = data_get($contentHotel, 'description.content', '');
        $address   = data_get($contentHotel, 'address.content', '');
        $country   = data_get($contentHotel, 'country.description.content', '');
        $city      = data_get($contentHotel, 'city.content', '');

        /* ---------------- HOUSE RULES ---------------- */

        // Defaults
        $checkInFrom  = null;
        $checkInTo    = null;
        $checkOutFrom = null;
        $checkOutTo   = null;

        $checkInDesc  = [];
        $checkOutDesc = [];

        /* -------- Check-in / Check-out from facilities (group 70) -------- */
        foreach ($facilities as $f) {
            $desc = data_get($f, 'description.content');

            if ($desc === 'Check-in hour') {
                $checkInFrom = data_get($f, 'timeFrom');
                $checkInTo   = data_get($f, 'timeTo');
                $checkInDesc[] = 'Check-in hour';
            }

            if ($desc === 'Check-out hour') {
                $checkOutFrom = data_get($f, 'timeFrom');
                $checkOutTo   = data_get($f, 'timeTo');
                $checkOutDesc[] = 'Check-out hour';
            }

            if ($desc === 'Early departure') {
                $checkOutDesc[] = 'Early departure';
            }
        }

        /* -------- Payment methods (facilityGroupCode = 30) -------- */
        $paymentMethods = collect($facilities)
            ->where('facilityGroupCode', 30)
            ->map(fn($f) => strtoupper(trim(data_get($f, 'description.content'))))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /* -------- Children & Beds (static text as per your response) -------- */
        $childrenAndBeds = [
            'summary' =>
            'Children above 18 will be charged as adults. To avoid problems and confusion, make sure your child is in the right category when searching for a stay.',
            'beds' => [],
            'note' =>
            'Cots/Extra Bed are NOT included in the total price and must be paid separately during your stay.',
        ];

        /* -------- Final houseRules array -------- */
        $houseRules = [
            'checkIn' => [
                'from'        => $checkInFrom,
                'to'          => $checkInTo,
                'description' => array_values(array_unique($checkInDesc)),
            ],
            'checkOut' => [
                'from'        => $checkOutFrom,
                'to'          => $checkOutTo,
                'description' => array_values(array_unique($checkOutDesc)),
            ],
            'cancellationPrepayment' =>
            'Cancellation and Prepayment policies vary according to accommodation type.Check the packages to see whatever you chose has a cancellation/prepayment policy or not.',
            'childrenAndBeds' => $childrenAndBeds,
            'ageRestrictions' => 'No age restrictions provided.',
            'pets' => 'Not provided',
            'paymentMethods' => $paymentMethods ?: [
                'AMERICAN EXPRESS',
                'MASTERCARD',
                'VISA',
            ],
        ];


        // ---------------- IMAGES ----------------
        $imagesByRoom = [];
        $genericRoomImages = [];

        foreach ($contentImages as $img) {
            $path = data_get($img, 'path');
            if (!$path) continue;

            $url = 'https://photos.hotelbeds.com/giata/' . ltrim($path, '/');

            if (!empty($img['roomCode'])) {
                $imagesByRoom[$img['roomCode']][] = $url;
            } else {
                $genericRoomImages[] = $url;
            }
        }

        // ---------------- ROOMS ----------------
        $roomsOut = [];
        $nights = max(
            Carbon::parse($data['checkIn'])->diffInDays(Carbon::parse($data['checkOut'])),
            1
        );

        foreach (data_get($hbHotel, 'rooms', []) as $room) {

            $roomCode = $room['code'];
            $rates    = $room['rates'];

            // Cheapest rate
            $lowestRate = collect($rates)->sortBy(fn($r) => (float)$r['net'])->first();
            if (!$lowestRate) continue;

            $contentRoom = $this->findContentRoom($contentRooms, $roomCode);

            // ---------- ROOM SIZE ----------
            $roomSizeSqm = collect(data_get($contentRoom, 'roomFacilities', []))
                ->firstWhere('facilityCode', 295)['number'] ?? null;

            // ---------- BED TYPE ----------
            $bedType = null;
            $charDesc = strtoupper(data_get($contentRoom, 'characteristic.description.content', ''));

            if (str_contains($charDesc, 'KING')) $bedType = 'King Bed';
            elseif (str_contains($charDesc, 'QUEEN')) $bedType = 'Queen Bed';
            elseif (str_contains($charDesc, 'TWIN')) $bedType = 'Twin Beds';
            elseif (str_contains($charDesc, 'DOUBLE')) $bedType = 'Double Bed';

            $countryCode = data_get($contentHotel, 'country.code')
                ?? data_get($contentHotel, 'country.isoCode')
                ?? Arr::get($hbHotel, 'countryCode');

            $cityCode = data_get($contentHotel, 'city.code')
                ?? Arr::get($hbHotel, 'destinationCode');

            // ---------- IMAGES ----------
            $roomImages = $imagesByRoom[$roomCode]
                ?? $imagesByRoom[data_get($contentRoom, 'roomCode', '')]
                ?? $genericRoomImages;

            $roomImages = array_slice($roomImages, 0, 5);

            // ---------- PRICING ----------
            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: (float)$lowestRate['net'],
                hotelMargin: null,
                country: data_get($contentHotel, 'country.code'),
                city: data_get($contentHotel, 'destination.code'),
            );

            // ---------- RATES ----------
            $hbRates = array_map(function ($r) use ($countryCode, $cityCode, $hbHotel, $hb) {

                $net = isset($r['net']) ? (float) $r['net'] : null;

                $pricing = $net
                    ? PricingService::calculatePriceForLocation(
                        vendorRate: $net,
                        hotelMargin: null,
                        country: $countryCode,
                        city: $cityCode,
                        context: [
                            'bookings_24h' => Arr::get($hbHotel, 'bookings24h', 0),
                            'market_rate' => null,
                            'competitor_rate' => null,
                            'conversion_rate_percent' => null,
                        ]
                    )
                    : null;

                // ---------------- TAXES (PER RATE) ----------------
                $rateTaxesOut   = [];
                $rateTaxesTotal = 0.0;

                $rateTaxes = Arr::get($r, 'taxes.taxes', []);
                if (!empty($rateTaxes)) {
                    foreach ($rateTaxes as $t) {
                        $rateTaxesOut[] = $t;

                        if (
                            array_key_exists('included', $t)
                            && $t['included'] === false
                            && isset($t['amount'])
                        ) {
                            $rateTaxesTotal += (float) $t['amount'];
                        }
                    }
                } elseif (isset($r['taxes']['allIncluded'])) {
                    $rateTaxesOut[] = [
                        'type'     => 'info',
                        'included' => (bool) $r['taxes']['allIncluded'],
                        'message'  => $r['taxes']['allIncluded']
                            ? 'All taxes are included'
                            : 'Local taxes may apply and are payable at the hotel',
                    ];
                } elseif (!empty($r['rateCommentsId'])) {
                    $comment = $hb->getRateComments($r['rateCommentsId']);
                    $rateTaxesOut[] = [
                        'type'    => 'comment',
                        'message' => data_get($comment, 'description')
                            ?? 'Local taxes may apply and are payable at the hotel',
                    ];
                }

                if (empty($rateTaxesOut)) {
                    $rateTaxesOut[] = [
                        'type'    => 'info',
                        'message' => 'Local taxes, if applicable, are payable at the hotel',
                    ];
                }
                // --------------------------------------------------

                // ---------------- CANCELLATION (PER RATE – FIXED) ----------------
                $rawCancellationPolicies = Arr::get($r, 'cancellationPolicies', []);

                $freeCancellation = false;
                $freeCancellationUntil = null;
                $now = Carbon::now('UTC');

                if (!empty($rawCancellationPolicies)) {
                    usort(
                        $rawCancellationPolicies,
                        fn($a, $b) => strtotime($a['from']) <=> strtotime($b['from'])
                    );

                    $firstPolicy = $rawCancellationPolicies[0];

                    if (
                        isset($firstPolicy['from'], $firstPolicy['amount']) &&
                        (float) $firstPolicy['amount'] === 0.0 &&
                        Carbon::parse($firstPolicy['from'], 'UTC')->isFuture()
                    ) {
                        $freeCancellation = true;
                        $freeCancellationUntil = $firstPolicy['from'];
                    }
                }
                // --------------------------------------------------

                return array_merge($r, [
                    // ✅ taxes
                    'taxes'      => $rateTaxesOut,
                    'taxesTotal' => $rateTaxesTotal,

                    // ✅ cancellation policies (raw, unchanged)
                    // 'cancellation' => $rawCancellationPolicies,

                    // ✅ frontend-safe flags
                    'freeCancellation'      => $freeCancellation,
                    'freeCancellationUntil' => $freeCancellationUntil,

                    // existing
                    'pricing' => $pricing,
                ]);
            }, $rates);



            $roomDescription =
                Arr::get($contentRoom, 'description')
                ?? Arr::get($contentRoom, 'characteristic.description.content')
                ?? Arr::get($room, 'name');

            $roomFacilities = collect(Arr::get($contentRoom, 'roomFacilities', []))
                ->map(function ($f) {
                    return trim(
                        Arr::get($f, 'description.content')
                            ?? Arr::get($f, 'description')
                    );
                })
                ->filter()
                ->values()
                ->toArray();

            $cancellationPolicies = Arr::get($lowestRate, 'cancellationPolicies', []);

            $isRefundable = false;
            $freeCancellationUntil = null;

            if (!empty($cancellationPolicies)) {
                usort(
                    $cancellationPolicies,
                    fn($a, $b) => strtotime($a['from']) <=> strtotime($b['from'])
                );

                foreach ($cancellationPolicies as $policy) {
                    if (
                        isset($policy['from'], $policy['amount']) &&
                        Carbon::parse($policy['from'], 'UTC')->isFuture() &&
                        (float) $policy['amount'] < (float) $lowestRate['net']
                    ) {
                        $isRefundable = true;
                        $freeCancellationUntil = $policy['from'];
                        break;
                    }
                }
            }

            $roomsOut[] = [
                'code'           => $roomCode,
                'name'           => $room['name'],
                'description'    => $roomDescription,
                'facilities'     => $roomFacilities,
                'vendorNet'      => (float)$lowestRate['net'],
                'marginPercent'  => $pricing['margin_percent'],
                'marginSource'   => $pricing['margin_source'],
                'scopeUsed'      => $pricing['scope_used'],
                'totalPrice'     => $pricing['final_price'],
                'pricePerNight'  => round($pricing['final_price'] / $nights, 2),
                'nights'         => $nights,
                'currency'       => $hbHotel['currency'],
                'roomSizeSqm'    => $roomSizeSqm,
                'bedType'        => $bedType ?? $charDesc,
                'roomFit'        => "{$lowestRate['adults']} adults",
                'remainingRooms' => $lowestRate['allotment'] ?? null,
                'images'         => $roomImages,
                'rateKey'        => $lowestRate['rateKey'],
                'rateType'       => $lowestRate['rateType'],
                'board'          => $lowestRate['boardName'],
                'refundable'     => $isRefundable,
                'hb_raw'         => ['rates' => $hbRates],
            ];
        }

        // ---------------- FINAL RESPONSE ----------------
        return response()->json([
            'success' => true,
            'hotel' => [
                'id' => $hbHotel['code'],
                'name' => $hbHotel['name'],
                'description' => $hotelDesc,
                'address' => "{$country}, {$city}, {$address}",
                'countryName' => $country,
                'cityName' => $city,
                'location' => $address,
                'destinationCode' => $hbHotel['destinationCode'],
                'destinationName' => $hbHotel['destinationName'],
                'categoryCode' => $hbHotel['categoryCode'],
                'categoryName' => $hbHotel['categoryName'],
                'zoneCode' => $hbHotel['zoneCode'],
                'zoneName' => $hbHotel['zoneName'],
                'latitude' => (float)$hbHotel['latitude'],
                'longitude' => (float)$hbHotel['longitude'],
                'minRate' => $hbHotel['minRate'],
                'maxRate' => $hbHotel['maxRate'],
                'currency' => $hbHotel['currency'],
                'images' => array_slice($genericRoomImages, 0, 5),
                'houseRules' => $houseRules,

            ],
            'rooms' => $roomsOut,
        ]);
    }

    private function findContentRoom(array $contentRooms, string $availabilityCode): ?array
    {
        foreach ($contentRooms as $r) {
            if (($r['roomCode'] ?? null) === $availabilityCode) {
                return $r;
            }
        }

        if (str_contains($availabilityCode, '.')) {
            [$type, $char] = explode('.', $availabilityCode, 2);

            foreach ($contentRooms as $r) {
                if (
                    data_get($r, 'type.code') === $type &&
                    data_get($r, 'characteristic.code') === $char
                ) {
                    return $r;
                }
            }
        }

        return null;
    }

    // --- priceQuote / checkAvailability / createSelection / categories / amenities remain unchanged from your version ---
    public function priceQuote($hotelId, Request $request)
    {
        $data = $request->validate([
            'rateKey'  => 'required|string',
            'quantity' => 'required|integer|min:1',
            'net'      => 'required|numeric',
            'currency' => 'required|string',
        ]);

        return response()->json([
            'success'       => true,
            'rateKey'       => $data['rateKey'],
            'quantity'      => $data['quantity'],
            'pricePerNight' => (float) $data['net'],
            'totalPrice'    => (float) $data['net'] * (int) $data['quantity'],
            'currency'      => $data['currency'],
        ]);
    }

    public function checkAvailability($hotelId, Request $request, HotelbedsService $hb)
    {
        $data = $request->validate([
            'rateKey' => 'required|string',
        ]);

        $payload = [
            'rooms' => [
                ['rateKey' => $data['rateKey']],
            ],
        ];

        $resp = $hb->checkRate($payload);

        if (isset($resp['error'])) {
            return response()->json([
                'success'        => false,
                'rateKey'        => $data['rateKey'],
                'isAvailable'    => false,
                'availableUnits' => 0,
                'error'          => $resp['error'],
                'raw'            => $resp['raw'] ?? null,
            ], 400);
        }

        $rate = Arr::get($resp, 'hotel.rooms.0.rates.0', []);

        return response()->json([
            'success'        => true,
            'rateKey'        => $rate['rateKey'] ?? $data['rateKey'],
            'isAvailable'    => true,
            'availableUnits' => $rate['allotment'] ?? null,
            'net'            => isset($rate['net']) ? (float) $rate['net'] : null,
            'currency'       => $rate['currency'] ?? null,
            'cancellation'   => $rate['cancellationPolicies'] ?? [],
            'raw'            => $resp,
        ]);
    }

    public function createSelection($hotelId, Request $request)
    {
        $data = $request->validate([
            'rooms'               => 'required|array|min:1',
            'rooms.*.rateKey'     => 'required|string',
            'rooms.*.quantity'    => 'required|integer|min:1',
            'checkIn'             => 'required|date',
            'checkOut'            => 'required|date|after:checkIn',
        ]);

        $selectionId = 'sel_' . uniqid();

        cache()->put("room_selection:{$selectionId}", [
            'user_id'  => optional($request->user())->id,
            'hotel_id' => $hotelId,
            'payload'  => $data,
        ], now()->addMinutes(15));

        return response()->json([
            'success'     => true,
            'selectionId' => $selectionId,
            'expiresIn'   => 900,
        ]);
    }

    public function categories()
    {
        return response()->json([
            'categories' => ['All', 'Deluxe Room', 'Superior Room', 'Suite', 'Family Room'],
        ]);
    }

    public function amenities()
    {
        return response()->json([
            'amenities' => ['Free WiFi', 'Parking', 'Breakfast included', 'Pool', 'Airport shuttle'],
        ]);
    }
}
