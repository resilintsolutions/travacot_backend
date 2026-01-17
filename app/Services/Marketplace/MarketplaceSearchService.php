<?php

namespace App\Services\Marketplace;

use App\Services\HotelbedsService;
use App\Services\PricingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MarketplaceSearchService
{
    public function __construct(
        private HotelbedsService $hotelbeds,
        private MarketplaceContentService $contentService
    ) {
    }

    public function search(array $params, bool $includeDiscounted): array
    {
        $location = $this->resolveLocation($params['query']);

        if (!$location) {
            return [];
        }

        $hotels = $this->availability($params, $location, 10);

        if (count($hotels) < 10) {
            $hotels = $this->availability($params, $location, 15);
        }

        return $this->mapHotels($hotels, $includeDiscounted);
    }

    protected function availability(array $params, array $location, int $radiusKm): array
    {
        $payload = [
            'stay' => [
                'checkIn' => $params['check_in'],
                'checkOut' => $params['check_out'],
            ],
            'occupancies' => [[
                'rooms' => 1,
                'adults' => $params['adults'],
                'children' => $params['children'],
            ]],
        ];

        if (!empty($location['lat']) && !empty($location['lon'])) {
            $payload['geolocation'] = [
                'latitude' => $location['lat'],
                'longitude' => $location['lon'],
                'radius' => $radiusKm,
                'unit' => 'km',
            ];
        } elseif (!empty($location['destination_code'])) {
            $payload['destination'] = [
                'code' => $location['destination_code'],
            ];
        }

        $resp = $this->hotelbeds->availability($payload);

        if (isset($resp['error'])) {
            return [];
        }

        return data_get($resp, 'hotels.hotels', []);
    }

    protected function mapHotels(array $hotels, bool $includeDiscounted): array
    {
        $results = [];

        foreach ($hotels as $hotel) {
            $vendorCode = $hotel['code'] ?? null;
            $name = data_get($hotel, 'name.content') ?? data_get($hotel, 'name');

            if (!$vendorCode || !$name) {
                continue;
            }

            $selectedRate = $this->selectLowestRate($hotel, $includeDiscounted);

            if (!$selectedRate) {
                continue;
            }

            $content = $this->contentService->getHotelContent($vendorCode);
            $contentHotel = data_get($content, 'hotel', []);

            $images = $this->mapImages(data_get($contentHotel, 'images', []));
            $latitude = data_get($contentHotel, 'coordinates.latitude') ?? data_get($hotel, 'latitude');
            $longitude = data_get($contentHotel, 'coordinates.longitude') ?? data_get($hotel, 'longitude');

            if (empty($images) || empty($latitude) || empty($longitude)) {
                continue;
            }

            $countryCode = data_get($contentHotel, 'country.isoCode') ?? data_get($hotel, 'countryCode');
            $cityCode = data_get($contentHotel, 'destination.code') ?? data_get($hotel, 'destinationCode');

            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: (float) data_get($selectedRate, 'net', 0),
                hotelMargin: null,
                country: $countryCode,
                city: $cityCode,
                context: [
                    'bookings_24h' => (int) data_get($hotel, 'bookings24h', 0),
                ]
            );

            $boardName = data_get($selectedRate, 'boardName');
            $mealIncluded = $boardName ? Str::contains(strtolower($boardName), 'breakfast') : false;
            $refundable = !(bool) data_get($selectedRate, 'nonRefundable', false);
            $allotment = data_get($selectedRate, 'allotment');

            $results[] = [
                'code' => $vendorCode,
                'name' => $name,
                'address' => trim(data_get($contentHotel, 'address.content', '')),
                'countryName' => data_get($contentHotel, 'country.description.content', ''),
                'cityName' => data_get($contentHotel, 'city.content', ''),
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'rating' => $this->ratingFromCategory(data_get($hotel, 'categoryCode')),
                'lowestRateNet' => (float) data_get($selectedRate, 'net', 0),
                'lowestRate' => $pricing['final_price'],
                'currency' => data_get($selectedRate, 'currency') ?? data_get($hotel, 'currency'),
                'marginPercent' => $pricing['margin_percent'],
                'marginSource' => $pricing['margin_source'],
                'msp' => $pricing['effective_min'],
                'refundable' => $refundable,
                'mealIncluded' => $mealIncluded,
                'roomsLeft' => $allotment,
                'images' => array_slice($images, 0, 5),
                'isDiscounted' => $this->isDiscountedRate($selectedRate),
            ];
        }

        return $results;
    }

    protected function selectLowestRate(array $hotel, bool $includeDiscounted): ?array
    {
        $best = null;

        foreach (data_get($hotel, 'rooms', []) as $room) {
            foreach (data_get($room, 'rates', []) as $rate) {
                $net = (float) data_get($rate, 'net', 0);
                if ($net <= 0) {
                    continue;
                }

                if (!$includeDiscounted && $this->isDiscountedRate($rate)) {
                    continue;
                }

                if (!$best || $net < (float) data_get($best, 'net', PHP_FLOAT_MAX)) {
                    $best = $rate;
                }
            }
        }

        return $best;
    }

    protected function isDiscountedRate(array $rate): bool
    {
        return !empty($rate['offers']);
    }

    protected function mapImages(array $images): array
    {
        $out = [];

        foreach ($images as $img) {
            $rawPath = $img['path'] ?? $img['imageUrl'] ?? null;
            if (!$rawPath) {
                continue;
            }

            $out[] = Str::startsWith($rawPath, ['http://', 'https://'])
                ? $rawPath
                : 'https://photos.hotelbeds.com/giata/' . ltrim($rawPath, '/');
        }

        return $out;
    }

    protected function ratingFromCategory(?string $categoryCode): ?int
    {
        if (!$categoryCode) {
            return null;
        }

        return (int) preg_replace('/\D/', '', $categoryCode);
    }

    protected function resolveLocation(string $query): ?array
    {
        $cacheKey = 'marketplace:geo:' . md5($query);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($query) {
            $geo = $this->geocodeQuery($query);

            if ($geo) {
                return $geo;
            }

            $destination = $this->resolveDestinationCode($query);

            if ($destination) {
                return ['destination_code' => $destination];
            }

            return null;
        });
    }

    protected function geocodeQuery(string $query): ?array
    {
        try {
            $resp = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'Travacot Marketplace'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (!$resp->successful()) {
                return null;
            }

            $result = $resp->json();

            if (empty($result[0]['lat']) || empty($result[0]['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $result[0]['lat'],
                'lon' => (float) $result[0]['lon'],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function resolveDestinationCode(string $query): ?string
    {
        $countries = Cache::remember('marketplace:countries', now()->addHours(24), function () {
            $resp = $this->hotelbeds->getHotelbedsCountries();
            return $resp['data'] ?? [];
        });

        $queryLower = strtolower($query);
        $countryCode = null;

        foreach ($countries as $country) {
            if (strtolower($country['code']) === $queryLower || strtolower($country['name']) === $queryLower) {
                $countryCode = $country['code'];
                break;
            }
        }

        if (!$countryCode) {
            return null;
        }

        $destinations = Cache::remember("marketplace:destinations:{$countryCode}", now()->addDays(7), function () use ($countryCode) {
            $resp = $this->hotelbeds->getHotelbedsDestinations(['countryCode' => $countryCode]);
            return $resp['data'] ?? [];
        });

        foreach ($destinations as $dest) {
            if (strtolower($dest['name']) === $queryLower || strtolower($dest['code']) === $queryLower) {
                return $dest['code'];
            }
        }

        return $destinations[0]['code'] ?? null;
    }
}
