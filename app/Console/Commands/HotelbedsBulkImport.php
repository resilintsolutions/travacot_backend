<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Services\HotelbedsService;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class HotelbedsBulkImport extends Command
{
    protected $signature = 'hotelbeds:bulk-import
        {--hotelIds= : Comma-separated Hotelbeds hotel codes}
        {--destination= : Hotelbeds destination code (e.g. PAR, LON)}
        {--checkIn= : Check-in date (Y-m-d). Default: +7 days}
        {--checkOut= : Check-out date (Y-m-d). Default: +8 days}
        {--rooms=1 : Number of rooms}
        {--adults=2 : Number of adults}
        {--children=0 : Number of children}';

    protected $description = 'Bulk import hotels from Hotelbeds with media & description';

    public function handle(HotelbedsService $hb, MediaService $mediaService)
    {
        $hotelIdsOpt = $this->option('hotelIds');
        $destination = $this->option('destination');

        if (!$hotelIdsOpt && !$destination) {
            $this->error('You must provide either --hotelIds or --destination');
            return self::FAILURE;
        }

        $checkIn  = $this->option('checkIn') ?: now()->addDays(7)->toDateString();
        $checkOut = $this->option('checkOut') ?: now()->addDays(8)->toDateString();

        $rooms    = (int) $this->option('rooms');
        $adults   = (int) $this->option('adults');
        $children = (int) $this->option('children');

        // Basic occupancy payload (enough for availability call used just for import)
        $payload = [
            'stay' => [
                'checkIn'  => $checkIn,
                'checkOut' => $checkOut,
            ],
            'occupancies' => [[
                'rooms'    => $rooms,
                'adults'   => $adults,
                'children' => $children,
            ]],
        ];

        if ($hotelIdsOpt) {
            $ids = array_filter(array_map('trim', explode(',', $hotelIdsOpt)));
            $payload['hotels'] = [
                'hotel' => array_map('intval', $ids),
            ];
            $this->info('Importing by hotelIds: ' . implode(', ', $ids));
        } else {
            $payload['destination'] = [
                'code' => $destination,
            ];
            $this->info("Importing by destination: {$destination}");
        }

        $this->line('Calling Hotelbeds availability API...');

        $resp = $hb->availability($payload);

        // If our wrapper caught an exception OR API returned top-level error
        if ((isset($resp['success']) && $resp['success'] === false) ||
            (isset($resp['error']) && !isset($resp['hotels']))
        ) {
            $msg = $resp['error']['message'] ?? 'Unknown Hotelbeds error';
            $this->error('Hotelbeds availability error: ' . $msg);
            return self::FAILURE;
        }

        $hotels = data_get($resp, 'hotels.hotels', []);
        if (!$hotels) {
            $this->warn('No hotels returned from Hotelbeds for given filters.');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($hotels) . ' hotels in availability response.');
        $count = 0;

        foreach ($hotels as $h) {
            $vendorId = data_get($h, 'code');
            if (!$vendorId) {
                $this->warn('Skipping hotel with no code.');
                continue;
            }

            $name = data_get($h, 'name.content') ?? data_get($h, 'name') ?? 'Unnamed';

            // Collect rates & currency from all rooms
            $allNets  = [];
            $currency = null;

            foreach (data_get($h, 'rooms', []) as $room) {
                foreach (data_get($room, 'rates', []) as $rate) {
                    if (isset($rate['net'])) {
                        $allNets[] = (float) $rate['net'];
                    }

                    if (!$currency && !empty($rate['currency'])) {
                        $currency = $rate['currency'];
                    }
                }
            }

            // Fallback currency
            $currency = $currency
                ?? data_get($h, 'currency')
                ?? data_get($resp, 'hotels.currency');

            $firstRateNet = data_get($h, 'rooms.0.rates.0.net');
            $lowestNet    = !empty($allNets) ? min($allNets) : $firstRateNet;

            /*
             * Fetch static content (description + images)
             * using Content API with fallback to availability/meta
             */
            $content = $hb->getHotelContent($vendorId);

            $hasContentHotel = !empty(data_get($content, 'hotel'));
            $countryName = data_get($content, 'hotel.country.description.content', '');
            $cityName = data_get($content, 'hotel.city.content', '');
            $countryCode = data_get($content, 'hotel.country.isoCode', '');
            $destinationCode = data_get($content, 'hotel.destination.code', '');
            $descriptionName = data_get($content, 'hotel.destination.name.content', '');
            $longitude = data_get($content, 'hotel.coordinates.longitude', '');
            $latitude = data_get($content, 'hotel.coordinates.latitude', '');
            $hotelPhones      = data_get($content, 'hotel.phones');
            $address = data_get($content, 'hotel.address.content');
            $currency = data_get($content, 'hotel.currency');
            $hotelEmail       = data_get($content, 'hotel.email');


            // Create or update hotel record
            $hotel = Hotel::updateOrCreate(
                [
                    'vendor'    => 'hotelbeds',
                    'vendor_id' => $vendorId,
                ],
                [
                    'name'        => $name,
                    'slug'        => Str::slug($name . '-' . $vendorId),
                    'country'     => $countryName,
                    'country_iso'     => $countryCode,
                    'destination_code'     => $destinationCode,
                    'destination_name'     => $descriptionName,
                    'address'           => $address,
                    'hotel_email'       => $hotelEmail,
                    'hotel_phones'      => $hotelPhones,
                    'longitude'     => $longitude,
                    'latitude'     => $latitude,
                    'city'        => $cityName,
                    'lowest_rate' => $lowestNet,
                    'highest_rate' => 0.0,
                    'currency'    => $currency,
                    'meta'        => $h,
                    'status'      => 'active',
                ]
            );



            if ($hasContentHotel) {
                // ✅ Use Content API description if hotel doesn't have one
                if (empty($hotel->description)) {
                    $desc = trim((string) data_get($content, 'hotel.description.content', ''));
                    if ($desc !== '') {
                        $hotel->description = $desc;
                        $hotel->save();
                    }
                }

                // ✅ Prefer Content API images
                $images   = data_get($content, 'hotel.images', []);
                $imported = 0;

                foreach ($images as $img) {
                    if ($imported >= 15) {
                        break;
                    }

                    $rawPath = $img['path'] ?? $img['imageUrl'] ?? $img['url'] ?? null;
                    if (!$rawPath) {
                        continue;
                    }

                    if (preg_match('#^https?://#i', $rawPath)) {
                        $url = $rawPath;
                    } else {
                        // Standard Hotelbeds photos base
                        $url = 'https://photos.hotelbeds.com/giata/' . ltrim($rawPath, '/');
                    }

                    $media = $mediaService->importForHotel($hotel->id, $url, 'images', [
                        'source'    => 'hotelbeds-content',
                        'vendor_id' => $vendorId,
                        'hotelbeds' => $img,
                    ]);

                    if ($media) {
                        $imported++;
                    }
                }
            } else {
                // ❌ Content API didn’t give a valid hotel node – try fallback from availability/meta
                $this->warn("Content API error or no hotel node for code {$vendorId}, trying fallback images from availability.");

                $fallbackImages = data_get($h, 'images', []);

                // Some responses keep images at room level
                if (empty($fallbackImages)) {
                    $fallbackImages = data_get($h, 'rooms.0.images', []);
                }

                $imported = 0;

                foreach ($fallbackImages as $img) {
                    if ($imported >= 10) {
                        break;
                    }

                    $rawPath = $img['path'] ?? $img['imageUrl'] ?? $img['url'] ?? null;
                    if (!$rawPath) {
                        continue;
                    }

                    if (preg_match('#^https?://#i', $rawPath)) {
                        $url = $rawPath;
                    } else {
                        $url = 'https://photos.hotelbeds.com/giata/' . ltrim($rawPath, '/');
                    }

                    $media = $mediaService->importForHotel($hotel->id, $url, 'images', [
                        'source'    => 'availability-meta',
                        'vendor_id' => $vendorId,
                        'hotelbeds' => $img,
                    ]);

                    if ($media) {
                        $imported++;
                    }
                }

                if ($imported === 0) {
                    $this->warn("No fallback images found for hotel code {$vendorId}.");
                }
            }

            $count++;
            $this->line("Imported/updated: {$hotel->name} (ID {$hotel->id})");
        }

        $this->info("Completed. Imported/updated {$count} hotels with description/images where available.");
        return self::SUCCESS;
    }
}
