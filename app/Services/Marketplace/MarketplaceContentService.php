<?php

namespace App\Services\Marketplace;

use App\Services\HotelbedsService;
use Illuminate\Support\Facades\Cache;

class MarketplaceContentService
{
    public function __construct(private HotelbedsService $hotelbeds)
    {
    }

    public function getHotelContent(int|string $hotelCode): array
    {
        $cacheKey = "marketplace:hotel-content:{$hotelCode}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($hotelCode) {
            return $this->hotelbeds->getHotelContent($hotelCode);
        });
    }
}
