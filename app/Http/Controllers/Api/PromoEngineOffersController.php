<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HotelbedsService;
use App\Services\PromoEngine\PromoEngineService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class PromoEngineOffersController extends Controller
{
    public function index(Request $request, HotelbedsService $hotelbeds, PromoEngineService $promoEngine)
    {
        $data = $request->validate([
            'destination' => 'nullable|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $payload = [
            'stay' => [
                'checkIn' => $data['check_in'],
                'checkOut' => $data['check_out'],
            ],
            'occupancies' => [[
                'rooms' => 1,
                'adults' => $data['adults'] ?? 2,
                'children' => $data['children'] ?? 0,
            ]],
        ];

        if (!empty($data['destination'])) {
            $payload['destination'] = ['code' => $data['destination']];
        }

        $resp = $hotelbeds->availability($payload);

        if (isset($resp['error'])) {
            return response()->json(['success' => false, 'error' => $resp['error']], 400);
        }

        $hotels = data_get($resp, 'hotels.hotels', []);
        $results = [];

        foreach ($hotels as $hotel) {
            $lowestRate = $this->lowestRate($hotel);
            if (!$lowestRate) {
                continue;
            }

            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: (float) data_get($lowestRate, 'net', 0),
                hotelMargin: null,
                country: data_get($hotel, 'countryCode'),
                city: data_get($hotel, 'destinationCode'),
                context: []
            );

            $decision = $promoEngine->decide(
                $pricing['margin_percent'] ?? 0,
                null,
                ['source' => 'ongoing_deals']
            );

            if ($decision['status'] !== 'applied') {
                continue;
            }

            $promoPrice = $promoEngine->applyToPrice(
                (float) data_get($lowestRate, 'net', 0),
                $pricing['margin_percent'] ?? 0,
                $decision['discount_percent'] ?? 0
            );

            $results[] = [
                'code' => data_get($hotel, 'code'),
                'name' => data_get($hotel, 'name.content') ?? data_get($hotel, 'name'),
                'currency' => data_get($hotel, 'currency'),
                'base_price' => $pricing['final_price'] ?? null,
                'promo_price' => $promoPrice['final_price'] ?? null,
                'promo' => $decision,
            ];
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    protected function lowestRate(array $hotel): ?array
    {
        $best = null;
        foreach (data_get($hotel, 'rooms', []) as $room) {
            foreach (data_get($room, 'rates', []) as $rate) {
                $net = (float) data_get($rate, 'net', 0);
                if ($net <= 0) {
                    continue;
                }
                if (!$best || $net < (float) data_get($best, 'net', PHP_FLOAT_MAX)) {
                    $best = $rate;
                }
            }
        }
        return $best;
    }
}
