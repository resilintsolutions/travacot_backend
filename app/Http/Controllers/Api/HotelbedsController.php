<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelbedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelbedsController extends Controller
{
    protected HotelbedsService $hb;

    public function __construct(HotelbedsService $hb)
    {
        $this->hb = $hb;
    }

    /**
     * GET /api/hotelbeds/hotels
     * Proxy to Hotelbeds and return mapped list for frontend consumption.
     */
    public function index(Request $req)
    {
        // optional filters from UI: checkIn, checkOut, query, hotelIds[]
        $payload = [];
        if ($req->filled('checkIn') && $req->filled('checkOut')) {
            $payload['stay'] = ['checkIn' => $req->checkIn, 'checkOut' => $req->checkOut];
        } else {
            $payload['stay'] = ['checkIn' => now()->addDays(7)->toDateString(), 'checkOut' => now()->addDays(8)->toDateString()];
        }
        $payload['occupancies'] = [['rooms' => 1, 'adults' => (int)$req->get('adults', 2)]];
        if ($req->filled('hotelIds')) {
            $payload['hotelIds'] = array_map('trim', explode(',', $req->hotelIds));
        } elseif ($req->filled('query')) {
            // Some endpoints support 'text' search; adapt as needed
            $payload['text'] = $req->query;
        }

        $resp = $this->hb->listHotels($payload);

        // Map supplier response to a consistent format for frontend
        $mapped = $this->mapListResponse($resp);

        return response()->json(['success' => true, 'data' => $mapped, 'raw' => $resp]);
    }

    /**
     * POST /api/hotelbeds/import
     * Body: { vendor_id: "...", payload: {...} } or pass full hotel object
     * This imports the selected hotel into local hotels table.
     */
    public function import(Request $req)
    {
        $req->validate([
            'vendor_id' => 'required|string',
            'hotel' => 'nullable|array',
            'payload' => 'nullable|array'
        ]);

        // If frontend passed full hotel payload, use it; else pull from Hotelbeds again
        $hotelPayload = $req->hotel ?? $req->payload ?? null;

        if (!$hotelPayload) {
            // fetch single hotel by vendor id (if endpoint supports single fetch)
            // We'll attempt to call listHotels with hotelIds filter
            $resp = $this->hb->listHotels([
                'stay' => ['checkIn' => now()->addDays(7)->toDateString(), 'checkOut' => now()->addDays(8)->toDateString()],
                'occupancies' => [['rooms' => 1, 'adults' => 2]],
                'hotelIds' => [$req->vendor_id]
            ]);
            // naive extraction
            $hotelPayload = data_get($resp, 'hotels.0', $resp);
        }

        // Map and upsert into local hotels table
        $name = data_get($hotelPayload, 'name') ?? data_get($hotelPayload, 'hotelName') ?? 'Unknown Hotel';
        $vendorId = $req->vendor_id;
        $country = data_get($hotelPayload, 'destination.country') ?? data_get($hotelPayload, 'country') ?? null;
        $city = data_get($hotelPayload, 'destination.city') ?? data_get($hotelPayload, 'city') ?? null;
        $lowestRate = null;
        // try to pick a rate if present
        $firstRate = data_get($hotelPayload, 'rooms.0.rates.0') ?? data_get($hotelPayload, 'rates.0') ?? null;
        if ($firstRate) {
            $lowestRate = $firstRate['net'] ?? ($firstRate['price'] ?? null);
        }

        $hotel = Hotel::updateOrCreate(
            ['vendor_id' => $vendorId, 'vendor' => 'hotelbeds'],
            [
                'name' => $name,
                'slug' => Str::slug($name . '-' . $vendorId),
                'country' => $country,
                'city' => $city,
                'lowest_rate' => $lowestRate,
                'currency' => $firstRate['currency'] ?? null,
                'description' => data_get($hotelPayload, 'description', null),
                'meta' => $hotelPayload,
                'status' => 'active'
            ]
        );

        return response()->json(['success' => true, 'hotel' => $hotel]);
    }

    /**
     * Map the raw supplier response to frontend-friendly structure
     */
    protected function mapListResponse($resp)
    {
        $out = [];
        $hotels = $resp['hotels'] ?? $resp['hotels'] ?? [];
        if (empty($hotels) && isset($resp['hotels'][0])) $hotels = $resp['hotels'];

        foreach ($hotels as $h) {
            $mapped = [
                'vendor_id' => $h['code'] ?? ($h['hotelCode'] ?? null),
                'name' => $h['name'] ?? ($h['hotelName'] ?? null),
                'country' => data_get($h, 'destination.country') ?? $h['country'] ?? null,
                'city' => data_get($h, 'destination.city') ?? $h['city'] ?? null,
                'rooms' => [],
                'raw' => $h
            ];

            $rooms = $h['rooms'] ?? $h['room'] ?? [];
            foreach ($rooms as $r) {
                $room = [
                    'name' => $r['name'] ?? $r['roomTypeName'] ?? null,
                    'rates' => []
                ];
                $rates = $r['rates'] ?? $r['rate'] ?? [];
                foreach ($rates as $rt) {
                    $room['rates'][] = [
                        'rateKey' => $rt['rateKey'] ?? null,
                        'net' => $rt['net'] ?? ($rt['price'] ?? null),
                        'currency' => $rt['currency'] ?? null,
                        'refundable' => $rt['nonRefundable'] ?? false
                    ];
                }
                $mapped['rooms'][] = $room;
            }
            $out[] = $mapped;
        }
        return $out;
    }

}
