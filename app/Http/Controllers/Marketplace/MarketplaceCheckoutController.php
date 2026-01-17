<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Services\Marketplace\MarketplaceReservationService;
use App\Services\Marketplace\MarketplaceContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MarketplaceCheckoutController extends Controller
{
    public function start(Request $request)
    {
        $data = $request->validate([
            'hotel_code' => 'required|integer',
            'rate_key' => 'required|string',
            'net' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'cancellationPolicies' => 'nullable|array',
        ]);

        $data['children'] = $data['children'] ?? 0;
        if (isset($data['cancellationPolicies']) && is_string($data['cancellationPolicies'])) {
            $decoded = json_decode($data['cancellationPolicies'], true);
            $data['cancellationPolicies'] = is_array($decoded) ? $decoded : [];
        }

        $request->session()->put('marketplace.checkout', $data);

        return redirect()->route('marketplace.checkout.show');
    }

    public function show(Request $request)
    {
        $checkout = $request->session()->get('marketplace.checkout');

        if (!$checkout) {
            return redirect()->route('marketplace.search.show')
                ->with('error', 'Please start a new booking from search.');
        }

        $user = $request->user();
        $methods = $user
            ? PaymentMethod::where('user_id', $user->id)->orderByDesc('is_default')->get()
            : collect();

        return view('marketplace.checkout', [
            'checkout' => $checkout,
            'user' => $user,
            'paymentMethods' => $methods,
        ]);
    }

    public function recheck(
        Request $request,
        MarketplaceReservationService $reservationService
    ) {
        $checkout = $request->session()->get('marketplace.checkout');

        if (!$checkout) {
            return response()->json(['status' => 'missing'], 422);
        }

        $rooms = [[
            'rate_key' => $checkout['rate_key'],
            'net' => $checkout['net'],
            'cancellationPolicies' => $checkout['cancellationPolicies'] ?? [],
        ]];

        $result = $reservationService->recheckRates($rooms);

        if ($result['status'] === 'failed') {
            return response()->json(['status' => 'failed', 'error' => $result['error']], 400);
        }

        if ($result['status'] === 'changed') {
            $rate = Arr::get($result, 'responses.0.rate', []);
            return response()->json([
                'status' => 'changed',
                'updated' => [
                    'rate_key' => $rate['rateKey'] ?? $checkout['rate_key'],
                    'net' => $rate['net'] ?? $checkout['net'],
                    'currency' => $rate['currency'] ?? $checkout['currency'],
                    'cancellationPolicies' => $rate['cancellationPolicies'] ?? [],
                ],
            ], 409);
        }

        $request->session()->put('marketplace.recheck_ok', now()->timestamp);

        return response()->json(['status' => 'ok']);
    }

    public function acceptRate(
        Request $request,
        MarketplaceReservationService $reservationService,
        MarketplaceContentService $contentService
    ) {
        $checkout = $request->session()->get('marketplace.checkout');

        if (!$checkout) {
            return response()->json(['status' => 'missing'], 422);
        }

        $data = $request->validate([
            'rate_key' => 'required|string',
            'net' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'cancellationPolicies' => 'nullable|array',
        ]);

        $checkout = array_merge($checkout, $data);
        $request->session()->put('marketplace.checkout', $checkout);
        $request->session()->put('marketplace.recheck_ok', now()->timestamp);

        return $this->createReservation($request, $reservationService, $contentService);
    }

    public function createReservation(
        Request $request,
        MarketplaceReservationService $reservationService,
        MarketplaceContentService $contentService
    ) {
        $checkout = $request->session()->get('marketplace.checkout');

        if (!$checkout) {
            return response()->json(['status' => 'missing'], 422);
        }

        $recheckAt = $request->session()->get('marketplace.recheck_ok');
        if (!$recheckAt || now()->timestamp - $recheckAt > 300) {
            return response()->json([
                'status' => 'recheck_required',
                'message' => 'Rate recheck required before payment.',
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $content = $contentService->getHotelContent($checkout['hotel_code']);

        $hotel = Hotel::updateOrCreate(
            ['vendor' => 'hotelbeds', 'vendor_id' => $checkout['hotel_code']],
            [
                'name' => data_get($content, 'hotel.name.content') ?? 'Unknown Hotel',
                'slug' => Str::slug((data_get($content, 'hotel.name.content') ?? 'hotel') . '-' . $checkout['hotel_code']),
                'country' => data_get($content, 'hotel.country.description.content'),
                'city' => data_get($content, 'hotel.city.content'),
                'country_iso' => data_get($content, 'hotel.country.isoCode'),
                'destination_code' => data_get($content, 'hotel.destination.code'),
                'destination_name' => data_get($content, 'hotel.destination.name.content'),
                'longitude' => data_get($content, 'hotel.coordinates.longitude'),
                'latitude' => data_get($content, 'hotel.coordinates.latitude'),
                'address' => data_get($content, 'hotel.address.content'),
                'currency' => $checkout['currency'],
                'status' => 'active',
            ]
        );

        $rooms = [[
            'rate_key' => $checkout['rate_key'],
            'rate' => [
                'rateKey' => $checkout['rate_key'],
                'net' => $checkout['net'],
                'currency' => $checkout['currency'],
                'cancellationPolicies' => $checkout['cancellationPolicies'] ?? [],
            ],
            'paxes' => $this->buildPaxes($checkout['adults'], $checkout['children'], $user),
        ]];

        $payload = [
            'user_id' => $user->id,
            'holder' => [
                'name' => $user->first_name ?? $user->name,
                'surname' => $user->last_name ?? '',
            ],
            'currency' => $checkout['currency'],
            'check_in' => $checkout['check_in'],
            'check_out' => $checkout['check_out'],
            'customer_email' => $user->email,
            'country_code' => data_get($content, 'hotel.country.isoCode'),
            'city_code' => data_get($content, 'hotel.destination.code'),
            'remark' => $request->input('remark'),
        ];

        $result = $reservationService->createReservationWithPaymentIntent(
            $payload,
            $hotel,
            $rooms
        );

        $request->session()->forget(['marketplace.recheck_ok']);

        return response()->json([
            'status' => 'created',
            'reservation_id' => $result['reservation']->id,
            'client_secret' => $result['intent']->client_secret,
        ]);
    }

    public function processing(Reservation $reservation)
    {
        return view('marketplace.processing', ['reservation' => $reservation]);
    }

    public function status(Reservation $reservation)
    {
        return response()->json([
            'status' => $reservation->status,
            'payment_status' => $reservation->payment_status,
        ]);
    }

    public function confirmation(Reservation $reservation)
    {
        return view('marketplace.confirmation', ['reservation' => $reservation]);
    }

    protected function buildPaxes(int $adults, int $children, $user): array
    {
        $paxes = [];

        for ($i = 0; $i < $adults; $i++) {
            $paxes[] = [
                'roomId' => 1,
                'type' => 'AD',
                'age' => 30,
                'name' => $user->first_name ?? $user->name,
                'surname' => $user->last_name ?? '',
            ];
        }

        for ($i = 0; $i < $children; $i++) {
            $paxes[] = [
                'roomId' => 1,
                'type' => 'CH',
                'age' => 8,
                'name' => $user->first_name ?? $user->name,
                'surname' => $user->last_name ?? '',
            ];
        }

        return $paxes;
    }
}
