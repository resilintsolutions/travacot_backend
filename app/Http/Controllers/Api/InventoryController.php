<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\PinnedHotel;
use App\Models\SupplierResponse;
use App\Services\HotelbedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\MediaService;

class InventoryController extends Controller
{
    /**
     * Local hotels list (from your DB).
     */
    public function index(Request $req)
    {
        $q = Hotel::query();

        if ($req->filled('query')) {
            $q->where('name', 'like', "%{$req->query}%");
        }

        if ($req->filled('vendor')) {
            $q->where('vendor', $req->vendor);
        }

        $per    = (int) $req->get('per', 20);
        $hotels = $q->orderBy('name')->paginate($per);

        return response()->json($hotels);
    }

    /**
     * Hotelbeds supplier search (Availability) – preview only.
     */
    public function supplierSearch(Request $req, HotelbedsService $hb)
    {
        $req->validate([
            'checkIn'  => 'required|date',
            'checkOut' => 'required|date',
        ]);

        $payload = [
            'stay' => [
                'checkIn'  => $req->checkIn,
                'checkOut' => $req->checkOut,
            ],
            'occupancies' => [[
                'rooms'    => (int) $req->get('rooms', 1),
                'adults'   => (int) $req->get('adults', 2),
                'children' => (int) $req->get('children', 0), // integer ✅
            ]],
        ];

        // Exactly one filter: hotels OR destination
        if ($req->filled('hotelIds')) {
            // Hotelbeds expects:
            // "hotels": { "hotel": [3424, 168] }
            $ids = array_map('trim', explode(',', $req->hotelIds));
            $ids = array_map('intval', $ids); // cast to int as in docs

            $payload['hotels'] = [
                'hotel' => $ids,
            ];
        } elseif ($req->filled('destination')) {
            // minimal destination filter – adapt as per your contract
            $payload['destination'] = [
                'code' => $req->destination,
            ];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You must send hotelIds or destination for supplier search.',
            ], 422);
        }

        $resp = $hb->listHotels($payload);

        SupplierResponse::create([
            'supplier'        => 'hotelbeds',
            'endpoint'        => '/hotel-api/1.0/hotels',
            'request_payload' => $payload,
            'response_body'   => json_encode($resp),
            'status_code'     => isset($resp['error']) ? 400 : 200,
        ]);

        if (isset($resp['error'])) {
            // If RequestException, $resp['raw'] will contain Hotelbeds JSON
            return response()->json([
                'success'        => false,
                'message'        => $resp['error']['message'] ?? 'Supplier error',
                'supplier_error' => $resp['error'],
                'raw'            => $resp['raw'] ?? null,
            ], 400);
        }

        $mapped = $this->mapListResponse($resp);

        return response()->json([
            'success' => true,
            'data'    => $mapped,
        ]);
    }

    /**
     * Import a single supplier hotel into local DB (by vendor_id / code).
     */
    public function importSingle(Request $req, HotelbedsService $hb, MediaService $mediaService)
    {
        $req->validate([
            'vendor_id' => 'required|string',
        ]);

        $vendorId = $req->vendor_id;

        // 1) AVAILABILITY (dynamic: prices, rooms, etc.)
        $payload = [
            'stay' => [
                'checkIn'  => now()->addDays(7)->toDateString(),
                'checkOut' => now()->addDays(8)->toDateString(),
            ],
            'occupancies' => [[
                'rooms'    => 1,
                'adults'   => 2,
                'children' => 0,
            ]],
            // Hotelbeds format: "hotels": { "hotel": [3424] }
            'hotels' => [
                'hotel' => [ (int) $vendorId ],
            ],
        ];

        $resp = $hb->availability($payload);

        SupplierResponse::create([
            'supplier'        => 'hotelbeds',
            'endpoint'        => '/hotel-api/1.0/hotels',
            'request_payload' => $payload,
            'response_body'   => json_encode($resp),
            'status_code'     => isset($resp['error']) ? 400 : 200,
        ]);

        if (isset($resp['error'])) {
            return response()->json([
                'success'        => false,
                'message'        => $resp['error']['message'] ?? 'Supplier error',
                'supplier_error' => $resp['error'],
                'raw'            => $resp['raw'] ?? null,
            ], 400);
        }

        // Response shape per docs: { "hotels": { "hotels": [ {...}, {...} ] } }
        $h = data_get($resp, 'hotels.hotels.0');
        if (! $h) {
            return response()->json([
                'success' => false,
                'message' => 'No hotel returned from supplier',
                'raw'     => $resp,
            ], 404);
        }

        $name        = $h['name'] ?? $h['hotelName'] ?? 'Unnamed';
        $vendor_code = $h['code'] ?? $vendorId;
        $firstRate   = data_get($h, 'rooms.0.rates.0');

        // 2) SAVE BASIC HOTEL FROM AVAILABILITY
        $hotel = Hotel::updateOrCreate(
            ['vendor' => 'hotelbeds', 'vendor_id' => $vendor_code],
            [
                'name'        => $name,
                'slug'        => Str::slug($name . '-' . $vendor_code),
                'country'     => $h['destinationCode'] ?? data_get($h, 'destination.country'),
                'city'        => $h['destinationName'] ?? data_get($h, 'destination.city'),
                'lowest_rate' => $firstRate['net'] ?? ($firstRate['price'] ?? null),
                'currency'    => $firstRate['currency'] ?? null,
                'meta'        => $h,
                'status'      => 'active',
            ]
        );

        
        // 3) CONTENT API (static: description, images, facilities, etc.)
        $content = $hb->getHotelContent($vendor_code);
        
        SupplierResponse::create([
            'supplier'        => 'hotelbeds',
            'endpoint'        => "/hotel-content-api/1.0/hotels/{$vendor_code}/details",
            'request_payload' => ['code' => $vendor_code],
            'response_body'   => json_encode($content),
            'status_code'     => isset($content['error']) ? 400 : 200,
        ]);
        if (!isset($content['error'])) {
            // 3a) Description
            $description = data_get($content, 'hotel.description.content')
            ?? data_get($content, 'hotel.description')
            ?? null;
            
            if ($description && empty($hotel->description)) {
                $hotel->description = $description;
                $hotel->save();
            }

            // 3b) Images
            $images = data_get($content, 'hotel.images', []);

            foreach ($images as $img) {
                // Typical Hotelbeds content: path is relative, e.g. "xx/yy/zz.jpg"
                $rawPath = $img['path'] ?? $img['imageUrl'] ?? null;
                if (! $rawPath) {
                    continue;
                }

                // If no scheme, prepend Hotelbeds photo base URL
                if (Str::startsWith($rawPath, ['http://', 'https://'])) {
                    $url = $rawPath;
                } else {
                    // adjust base if your account uses a different photos domain
                    $url = 'https://photos.hotelbeds.com/giata/' . ltrim($rawPath, '/');
                }

                // Avoid duplicates by external_url
                $exists = $hotel->media()
                    ->where('external_url', $url)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $mediaService->importForHotel($hotel->id, $url, 'images', [
                    'source'        => 'hotelbeds-content',
                    'type'          => $img['type']         ?? null,
                    'order'         => $img['order']        ?? null,
                    'visualOrder'   => $img['visualOrder']  ?? null,
                    'roomCode'      => $img['roomCode']     ?? null,
                    'characteristicCode' => $img['characteristicCode'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'hotel'   => $hotel->load('media'),
        ]);
    }

    public function pin(Request $req)
    {
        $req->validate([
            'hotel_id' => 'required|exists:hotels,id',
        ]);

        $hotelId = $req->hotel_id;
        $exists  = PinnedHotel::where('hotel_id', $hotelId)->first();

        if ($exists) {
            return response()->json(['success' => true, 'pinned' => $exists]);
        }

        $pinned = PinnedHotel::create([
            'hotel_id' => $hotelId,
            'user_id'  => $req->user()?->id,
            'position' => (PinnedHotel::max('position') ?? 0) + 1,
        ]);

        return response()->json(['success' => true, 'pinned' => $pinned]);
    }

    public function unpin(PinnedHotel $pinned)
    {
        $pinned->delete();

        return response()->json(['success' => true]);
    }

    public function pinnedList()
    {
        $list = PinnedHotel::with('hotel')
            ->orderBy('position')
            ->get();

        return response()->json(['data' => $list]);
    }

    public function contentHealth(Request $req)
    {
        $q = Hotel::query();

        if ($req->filled('vendor')) {
            $q->where('vendor', $req->vendor);
        }

        $hotels = $q->limit(200)->get();
        $out    = [];

        foreach ($hotels as $h) {
            $issues = [];

            if (empty($h->meta)) {
                $issues[] = 'missing_meta';
            }

            if (empty($h->description)) {
                $issues[] = 'no_description';
            }

            $hasImage = false;
            if (!empty($h->meta) && (data_get($h->meta, 'images.0') || data_get($h->meta, 'images'))) {
                $hasImage = true;
            }
            if (!$hasImage) {
                $issues[] = 'no_images';
            }

            $out[] = [
                'id'     => $h->id,
                'name'   => $h->name,
                'vendor' => $h->vendor,
                'issues' => $issues,
            ];
        }

        return response()->json(['data' => $out]);
    }

    /**
     * Map Hotelbeds availability response to a simpler structure for UI.
     */
    protected function mapListResponse(array $resp): array
    {
        $out = [];

        // Availability response: { "hotels": { "hotels": [ ... ] } }
        $hotelsWrapper = $resp['hotels']['hotels'] ?? [];
        $hotels        = is_array($hotelsWrapper) ? $hotelsWrapper : [];

        foreach ($hotels as $h) {
            $rooms = [];
            $rlist = $h['rooms'] ?? [];

            foreach ($rlist as $r) {
                $rates = [];
                $rr    = $r['rates'] ?? [];

                foreach ($rr as $rt) {
                    $rates[] = [
                        'rateKey'    => $rt['rateKey'] ?? null,
                        'net'        => $rt['net'] ?? ($rt['price'] ?? null),
                        'currency'   => $rt['currency'] ?? null,
                        'refundable' => ($rt['nonRefundable'] ?? false) ? false : true,
                    ];
                }

                $rooms[] = [
                    'name'  => $r['name'] ?? $r['roomTypeName'] ?? null,
                    'rates' => $rates,
                ];
            }

            $out[] = [
                'vendor_id' => $h['code'] ?? null,
                'name'      => $h['name'] ?? null,
                'country'   => $h['destinationCode'] ?? null,
                'city'      => $h['destinationName'] ?? null,
                'rooms'     => $rooms,
                'raw'       => $h,
            ];
        }

        return $out;
    }

        public function uploadMedia(Request $request, Hotel $hotel)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB
            'collection' => 'nullable|string'
        ]);

        $file = $request->file('file');
        $collection = $request->get('collection', 'images');

        $path = $file->store("hotels/{$hotel->id}/{$collection}", 'public');

        $media = $hotel->media()->create([
            'collection' => $collection,
            'file_name'  => $file->getClientOriginalName(),
            'path'       => $path,
            'mime_type'  => $file->getClientMimeType(),
            'size'       => $file->getSize(),
        ]);

        return redirect()->back();
        // return response()->json(['success' => true, 'media' => $media]);
    }
}
