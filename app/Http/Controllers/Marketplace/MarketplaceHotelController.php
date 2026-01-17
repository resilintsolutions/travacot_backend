<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\HotelbedsService;
use App\Services\Marketplace\MarketplaceContentService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MarketplaceHotelController extends Controller
{
    public function show(
        int $hotelCode,
        Request $request,
        HotelbedsService $hotelbeds,
        MarketplaceContentService $contentService
    ) {
        $data = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $data['children'] = $data['children'] ?? 0;

        $payload = [
            'stay' => [
                'checkIn' => $data['check_in'],
                'checkOut' => $data['check_out'],
            ],
            'occupancies' => [[
                'rooms' => 1,
                'adults' => $data['adults'],
                'children' => $data['children'],
            ]],
            'hotels' => [
                'hotel' => [$hotelCode],
            ],
        ];

        $resp = $hotelbeds->availability($payload);

        if (isset($resp['error'])) {
            return back()->with('error', 'Unable to load availability for this hotel.');
        }

        $hbHotel = data_get($resp, 'hotels.hotels.0');

        if (!$hbHotel) {
            return back()->with('error', 'No availability found for this hotel.');
        }

        $content = $contentService->getHotelContent($hotelCode);
        $contentHotel = data_get($content, 'hotel', []);

        $rooms = $this->mapRooms(
            $hbHotel,
            $contentHotel,
            $data,
            $request->user() !== null
        );

        return view('marketplace.hotel', [
            'hotel' => $this->mapHotel($hbHotel, $contentHotel),
            'rooms' => $rooms,
            'search' => $data,
        ]);
    }

    protected function mapHotel(array $hbHotel, array $contentHotel): array
    {
        return [
            'code' => data_get($hbHotel, 'code'),
            'name' => data_get($hbHotel, 'name'),
            'description' => data_get($contentHotel, 'description.content'),
            'address' => data_get($contentHotel, 'address.content'),
            'countryName' => data_get($contentHotel, 'country.description.content'),
            'cityName' => data_get($contentHotel, 'city.content'),
            'latitude' => (float) (data_get($contentHotel, 'coordinates.latitude') ?? data_get($hbHotel, 'latitude')),
            'longitude' => (float) (data_get($contentHotel, 'coordinates.longitude') ?? data_get($hbHotel, 'longitude')),
            'images' => $this->mapImages(data_get($contentHotel, 'images', [])),
            'rating' => (int) preg_replace('/\D/', '', (string) data_get($hbHotel, 'categoryCode')),
            'amenities' => $this->mapAmenities(data_get($contentHotel, 'facilities', [])),
            'houseRules' => $this->mapHouseRules(data_get($contentHotel, 'facilities', [])),
        ];
    }

    protected function mapRooms(array $hbHotel, array $contentHotel, array $search, bool $includeDiscounted): array
    {
        $roomsOut = [];
        $nights = max(
            Carbon::parse($search['check_in'])->diffInDays(Carbon::parse($search['check_out'])),
            1
        );

        foreach (data_get($hbHotel, 'rooms', []) as $room) {
            $rates = [];

            foreach (data_get($room, 'rates', []) as $rate) {
                if (!$includeDiscounted && !empty($rate['offers'])) {
                    continue;
                }

                $pricing = PricingService::calculatePriceForLocation(
                    vendorRate: (float) data_get($rate, 'net', 0),
                    hotelMargin: null,
                    country: data_get($contentHotel, 'country.isoCode'),
                    city: data_get($contentHotel, 'destination.code'),
                    context: [
                        'bookings_24h' => (int) data_get($hbHotel, 'bookings24h', 0),
                    ]
                );

                $rates[] = [
                    'rate_key' => $rate['rateKey'] ?? null,
                    'net' => (float) data_get($rate, 'net', 0),
                    'currency' => data_get($rate, 'currency') ?? data_get($hbHotel, 'currency'),
                    'board' => data_get($rate, 'boardName'),
                    'payment_type' => data_get($rate, 'paymentType'),
                    'refundable' => !(bool) data_get($rate, 'nonRefundable', false),
                    'allotment' => data_get($rate, 'allotment'),
                    'cancellationPolicies' => data_get($rate, 'cancellationPolicies', []),
                    'pricing' => $pricing,
                    'offers' => data_get($rate, 'offers', []),
                ];
            }

            if (!$rates) {
                continue;
            }

            $lowest = collect($rates)->sortBy('net')->first();

            $roomsOut[] = [
                'code' => $room['code'],
                'name' => $room['name'],
                'rates' => $rates,
                'lowestRate' => $lowest,
                'pricePerNight' => $lowest
                    ? round(($lowest['pricing']['final_price'] ?? $lowest['net']) / $nights, 2)
                    : null,
            ];
        }

        return $roomsOut;
    }

    protected function mapAmenities(array $facilities): array
    {
        return collect($facilities)
            ->map(fn ($f) => Arr::get($f, 'description.content'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected function mapHouseRules(array $facilities): array
    {
        $rules = [];
        foreach ($facilities as $facility) {
            $desc = Arr::get($facility, 'description.content');
            if ($desc === 'Check-in hour' || $desc === 'Check-out hour' || $desc === 'Early departure') {
                $rules[] = $desc;
            }
        }

        return array_values(array_unique($rules));
    }

    protected function mapImages(array $images): array
    {
        return collect($images)
            ->map(function ($img) {
                $raw = $img['path'] ?? $img['imageUrl'] ?? null;
                if (!$raw) {
                    return null;
                }
                return str_starts_with($raw, 'http')
                    ? $raw
                    : 'https://photos.hotelbeds.com/giata/' . ltrim($raw, '/');
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
