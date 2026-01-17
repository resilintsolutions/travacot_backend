<?php

namespace App\Services\Marketplace;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Services\HotelbedsService;
use App\Services\PricingService;
use App\Services\StripeService;
use Illuminate\Support\Arr;

class MarketplaceReservationService
{
    public function __construct(
        private HotelbedsService $hotelbeds,
        private StripeService $stripe
    ) {
    }

    public function recheckRates(array $rooms): array
    {
        $responses = [];

        foreach ($rooms as $index => $room) {
            $rateKey = $room['rate_key'];
            $payload = [
                'rooms' => [
                    ['rateKey' => $rateKey],
                ],
            ];

            $resp = $this->attemptCheckRate($payload);

            if (isset($resp['error'])) {
                return [
                    'status' => 'failed',
                    'error' => $resp['error'],
                ];
            }

            $rate = Arr::get($resp, 'hotel.rooms.0.rates.0', []);
            $responses[] = [
                'rate' => $rate,
                'raw' => $resp,
                'index' => $index,
            ];
        }

        $changed = $this->ratesChanged($rooms, $responses);

        return [
            'status' => $changed ? 'changed' : 'success',
            'responses' => $responses,
        ];
    }

    public function createReservationWithPaymentIntent(
        array $data,
        Hotel $hotel,
        array $roomsWithRates
    ): array {
        $pricingBreakdown = [];
        $roomsPayload = [];
        $rateSnapshots = [];
        $totalVendorNet = 0.0;
        $totalSelling = 0.0;
        $totalMarkup = 0.0;

        foreach ($roomsWithRates as $room) {
            $rate = $room['rate'];
            $net = (float) ($rate['net'] ?? 0);

            $roomsPayload[] = [
                'rateKey' => $rate['rateKey'] ?? $room['rate_key'],
                'paxes' => $room['paxes'] ?? [],
            ];

            $rateSnapshots[] = $rate;

            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: $net,
                hotelMargin: null,
                country: $data['country_code'] ?? null,
                city: $data['city_code'] ?? null,
                context: []
            );

            $pricingBreakdown[] = [
                'rateKey' => $rate['rateKey'] ?? $room['rate_key'],
                'vendor_net' => $pricing['vendor_net'],
                'selling_price' => $pricing['selling_price'],
                'final_price' => $pricing['final_price'],
                'margin_percent' => $pricing['margin_percent'],
                'scope_used' => $pricing['scope_used'],
                'msp_scope' => $pricing['msp_scope'],
            ];

            $totalVendorNet += $pricing['vendor_net'];
            $totalSelling += $pricing['final_price'];
            $totalMarkup += ($pricing['final_price'] - $pricing['vendor_net']);
        }

        $sellAmount = round($totalSelling, 2);

        $reservation = Reservation::create([
            'hotel_id' => $hotel->id,
            'user_id' => $data['user_id'],
            'guest_info' => [
                'holder' => $data['holder'],
                'rooms' => $roomsPayload,
                'rate_snapshot' => $rateSnapshots,
                'pricing_breakdown' => $pricingBreakdown,
                'countryCode' => $data['country_code'] ?? null,
                'cityCode' => $data['city_code'] ?? null,
                'remark' => $data['remark'] ?? null,
            ],
            'total_price' => $sellAmount,
            'markup_amount' => round($totalMarkup, 2),
            'currency' => $data['currency'],
            'status' => 'pending',
            'payment_status' => 'pending_payment',
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'customer_name' => $data['holder']['name'] . ' ' . $data['holder']['surname'],
            'customer_email' => $data['customer_email'] ?? null,
            'booking_channel' => 'Marketplace',
        ]);

        $intent = $this->stripe->createPaymentIntent(
            $sellAmount,
            $data['currency'],
            ['reservation_id' => $reservation->id]
        );

        $reservation->stripe_payment_intent_id = $intent->id;
        $reservation->payment_status = $intent->status ?? 'pending_payment';
        $reservation->save();

        return [
            'reservation' => $reservation,
            'intent' => $intent,
        ];
    }

    protected function ratesChanged(array $rooms, array $responses): bool
    {
        foreach ($responses as $idx => $response) {
            $rate = $response['rate'];
            $original = $rooms[$response['index']] ?? [];

            $originalNet = (float) ($original['net'] ?? 0);
            $newNet = (float) ($rate['net'] ?? 0);

            if ($originalNet > 0 && $newNet > 0 && $originalNet !== $newNet) {
                return true;
            }

            $originalCancel = $original['cancellationPolicies'] ?? [];
            $newCancel = $rate['cancellationPolicies'] ?? [];

            if (json_encode($originalCancel) !== json_encode($newCancel)) {
                return true;
            }
        }

        return false;
    }

    protected function attemptCheckRate(array $payload): array
    {
        $resp = $this->hotelbeds->checkRate($payload);

        if (isset($resp['error'])) {
            $resp = $this->hotelbeds->checkRate($payload);
        }

        return $resp;
    }
}
