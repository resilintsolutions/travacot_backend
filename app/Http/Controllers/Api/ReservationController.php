<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\SupplierResponse;
use App\Services\HotelbedsService;
use App\Services\PricingService;
use App\Services\StripeService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\HealthEventLog;


class ReservationController extends Controller
{
    /**
     * List reservations (for admin/API use)
     */
    public function index(Request $req)
    {
        $userEmail = $req->user()->email;

        $q = Reservation::query()
            ->with('hotel')
            ->where('customer_email', $userEmail);

        if ($req->filled('status')) {
            $q->where('status', $req->status);
        }

        if ($req->filled('hotel_id')) {
            $q->where('hotel_id', $req->hotel_id);
        }

        $reservations = $q->orderByDesc('created_at')->paginate(20);

        $data = $reservations->getCollection()->map(function ($reservation) {

            $hotelModel = $reservation->hotel;

            $hotelMeta = [
                'name'     => $hotelModel->name ?? null,
                'currency' => $reservation->currency ?? 'EUR',
            ];


            $raw = is_array($reservation->raw_response)
                ? $reservation->raw_response
                : [];


            $hotel = is_array(data_get($raw, 'hotel'))
                ? data_get($raw, 'hotel')
                : [];

            $room  = $hotel['rooms'][0] ?? [];
            $rate  = $room['rates'][0] ?? [];

            $checkInRaw  = data_get($raw, 'hotel.checkIn') ?? $reservation->check_in;
            $checkOutRaw = data_get($raw, 'hotel.checkOut') ?? $reservation->check_out;


            $checkIn  = $checkInRaw ? Carbon::parse($checkInRaw) : null;
            $checkOut = $checkOutRaw ? Carbon::parse($checkOutRaw) : null;


            $address = trim(implode(', ', array_filter([
                $hotelModel->address ?? null,
                $hotelModel->city ?? null,
                $hotelModel->country ?? null,
            ])));

            return [
                'hotel' => [
                    'name'      => $hotelMeta['name'],
                    'image' => $hotelModel->featured_image,
                    'status' => match ($reservation->status) {
                        'failed_booking' => 'Booking Failed',
                        'payment_failed' => 'Payment Failed',
                        default => ucfirst(strtolower($raw['status'] ?? $reservation->status)),
                    },
                    'check_in'  => $checkIn?->format('M d, Y'),
                    'check_out' => $checkOut?->format('M d, Y'),
                    'nights'    => $checkIn && $checkOut ? $checkIn->diffInDays($checkOut) : 0,
                ],

                'guest' => [
                    'name'   => trim(($raw['holder']['name'] ?? '') . ' ' . ($raw['holder']['surname'] ?? '')),
                    'email'  => $reservation->customer_email ?? null,
                    'guests' => ($rate['adults'] ?? 0) + ($rate['children'] ?? 0),
                ],

                'reservation' => [
                    'id'                   => $reservation->id,
                    'confirmation_number'  => $raw['reference'] ?? null,
                    'room_type'            => $room['name'] ?? null,
                    'board'                => $rate['boardName'] ?? null,
                    'total_price' => [
                        'amount'   => round($reservation->total_price, 2),
                        'currency' => $reservation->currency ?? 'EUR',
                        'status'   => 'confirmed',
                    ],

                    'created_at' => $reservation->created_at->format('M d, Y'),
                ],

                'hotel_information' => [
                    'address' => $address ?: null,
                    'phone'   => $hotelModel->hotel_phones ?? null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $reservations->currentPage(),
                'last_page'    => $reservations->lastPage(),
                'per_page'     => $reservations->perPage(),
                'total'        => $reservations->total(),
            ]
        ]);
    }

    /**
     * Show a single reservation
     */

    public function show(Reservation $reservation)
    {
        $reservation->load('hotel');

        $hotelModel = $reservation->hotel;
        $hotelMeta = [
            'name'     => $hotelModel->name ?? null,
            'currency' => $reservation->currency ?? 'EUR',
        ];

        $adrs = trim(implode(', ', array_filter([
            $hotelModel->address ?? null,
            $hotelModel->city ?? null,
            $hotelModel->country ?? null,
        ])));

        $phones = $hotelModel->hotel_phones ?? null;


        $raw = is_array($reservation->raw_response)
            ? $reservation->raw_response
            : [];

        $hotel = is_array(data_get($raw, 'hotel'))
            ? data_get($raw, 'hotel')
            : [];


        $room  = $hotel['rooms'][0] ?? [];
        $rate  = $room['rates'][0] ?? [];

        $checkInRaw  = data_get($raw, 'hotel.checkIn') ?? $reservation->check_in;
        $checkOutRaw = data_get($raw, 'hotel.checkOut') ?? $reservation->check_out;


        $checkIn  = $checkInRaw ? Carbon::parse($checkInRaw) : null;
        $checkOut = $checkOutRaw ? Carbon::parse($checkOutRaw) : null;


        return response()->json([
            'success' => true,
            'data' => [
                'hotel' => [
                    'name'      => $hotelMeta['name'],
                    'image' => $hotelModel->featured_image,
                    'status' => match ($reservation->status) {
                        'failed_booking' => 'Booking Failed',
                        'payment_failed' => 'Payment Failed',
                        default => ucfirst(strtolower($raw['status'] ?? $reservation->status)),
                    },

                    'check_in'  => $checkIn?->format('M d, Y'),
                    'check_out' => $checkOut?->format('M d, Y'),
                    'nights'    => ($checkIn && $checkOut)
                        ? $checkIn->diffInDays($checkOut)
                        : null,

                ],

                'guest' => [
                    'name'   => trim(($raw['holder']['name'] ?? '') . ' ' . ($raw['holder']['surname'] ?? '')),
                    'email'  => $reservation->customer_email ?? null,
                    'guests' => ($rate['adults'] ?? 0) + ($rate['children'] ?? 0),
                ],

                'reservation' => [
                    'confirmation_number' => $raw['reference'] ?? null,
                    'room_type' => $room['name'] ?? null,
                    'board'     => $rate['boardName'] ?? null,
                    'total_price' => [
                        'amount'   => round($reservation->total_price, 2),
                        'currency' => $hotel['currency'] ?? 'EUR',
                        'status'   => 'confirmed',
                    ],
                ],

                'hotel_information' => [
                    'address'        => $adrs ?? null,
                    'phone'          => $phones ?? null,
                    'check_in_time'  => $checkIn?->format('M d, Y \a\t h:i A'),
                    'check_out_time' => $checkOut?->format('M d, Y \a\t h:i A'),

                ],

                'refunds' => [
                    "If you are eligible for a refund, it will be processed according to the hotel's rules.",
                    "Refunds may take up to 5 to 7 business days to appear on your account, depending on the payment method and banking processes."
                ],
            ]
        ]);
    }

    /**
     * Preview Hotelbeds rates
     * BEST PRACTICE: ONE CheckRate call per rateKey.
     *
     * POST /api/reservations/preview
     */
    public function preview(Request $request, HotelbedsService $hb)
    {
        $data = $request->validate([
            'rate_key'    => 'sometimes|string',
            'rate_keys'   => 'sometimes|array|min:1',
            'rate_keys.*' => 'string',
        ]);

        $keys = !empty($data['rate_keys'])
            ? $data['rate_keys']
            : (!empty($data['rate_key']) ? [$data['rate_key']] : []);

        if (empty($keys)) {
            return response()->json([
                'success' => false,
                'message' => 'rate_key or rate_keys is required',
            ], 422);
        }

        $previewRooms = [];
        $totalNet     = 0.0;
        $currency     = null;
        $hotelCode    = null;
        $hotelName    = null;
        $checkIn      = null;
        $checkOut     = null;

        foreach ($keys as $idx => $key) {
            $payload = [
                'rooms' => [
                    ['rateKey' => $key],
                ],
            ];

            $resp = $hb->checkRate($payload);
            $rate = Arr::get($resp, 'hotel.rooms.0.rates.0', []);

            $priceChanged = isset($rate['net']) && $rate['net'] != ($originalNet ?? null);
            $roomGone     = empty($rate);

            HealthEventLog::create([
                'event_date' => now()->toDateString(),
                'domain' => 'recheck',
                'action' => 'checkrate',
                'status' => isset($resp['error']) ? 'failure' : 'success',
                'meta' => [
                    'price_changed' => $priceChanged,
                    'room_unavailable' => $roomGone,
                    'supplier_error' => $resp['error']['code'] ?? null,
                ]
            ]);

            SupplierResponse::create([
                'supplier'        => 'hotelbeds',
                'endpoint'        => '/hotel-api/1.0/checkrates',
                'request_payload' => $payload,
                'response_body'   => json_encode($resp),
                'status_code'     => isset($resp['error']) ? 400 : 200,
            ]);

            if (isset($resp['error'])) {
                return response()->json([
                    'success' => false,
                    'error'   => $resp['error'],
                    'raw'     => $resp['raw'] ?? null,
                ], 400);
            }

            $room = Arr::get($resp, 'hotel.rooms.0', []);
            $rate = Arr::get($room, 'rates.0', []);

            $net  = (float) ($rate['net'] ?? 0);
            $totalNet += $net;

            $currency  = $currency  ?: Arr::get($rate, 'currency', Arr::get($resp, 'hotel.currency'));
            $hotelCode = $hotelCode ?: Arr::get($resp, 'hotel.code');
            $hotelName = $hotelName ?: Arr::get($resp, 'hotel.name');
            $checkIn   = $checkIn   ?: Arr::get($resp, 'hotel.checkIn');
            $checkOut  = $checkOut  ?: Arr::get($resp, 'hotel.checkOut');

            $previewRooms[] = [
                'room_index'   => $idx,
                'room_name'    => Arr::get($room, 'name'),
                'board'        => Arr::get($rate, 'boardName'),
                'rate_key'     => $rate['rateKey'] ?? $key,
                'net'          => $net,
                'selling_rate' => Arr::get($rate, 'sellingRate') ?? $net,
                'cancellation' => Arr::get($rate, 'cancellationPolicies', []),
                'raw'          => $resp,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'hotel_code' => $hotelCode,
                'hotel_name' => $hotelName,
                'check_in'   => $checkIn,
                'check_out'  => $checkOut,
                'currency'   => $currency,
                'total_net'  => $totalNet,
                'rooms'      => $previewRooms,
            ],
        ]);
    }

    /**
     * Stripe checkout: create reservation + PaymentIntent, but DO NOT book yet.
     *
     * POST /api/reservations/checkout
     *
     * Note: Added MediaService param so we can import hotel if missing.
     */
    public function checkout(
        Request $req,
        HotelbedsService $hb,
        StripeService $stripe,
        MediaService $mediaService
    ) {
        $data = $req->validate([
            'hotel_id'              => 'required|integer',

            'rooms'                 => 'required|array|min:1',
            'rooms.*.rate_key'      => 'required|string',
            'rooms.*.rate_type'     => 'required|string|in:BOOKABLE,RECHECK',
            'rooms.*.net'           => 'nullable|numeric',
            'rooms.*.paxes'         => 'required|array|min:1',
            'rooms.*.paxes.*.type'  => 'in:AD,CH',
            'rooms.*.paxes.*.age'   => 'integer|min:0',

            'holder.name'           => 'required|string',
            'holder.surname'        => 'required|string',

            'client_reference'      => 'nullable|string',
            'remark'                => 'nullable|string',
            'channel'               => 'nullable|string|max:50',

            'currency'              => 'required|string|size:3',

            'countryCode'           => 'required|string',
            'cityCode'              => 'required|string',

            'customer_email'        => 'nullable|email',
            'check_in'              => 'nullable|date',
            'check_out'             => 'nullable|date|after:check_in',
        ]);

        /* ----------------------------------------------------
        * 1) Resolve hotel (import if missing)
        * ---------------------------------------------------- */
        $hotel = Hotel::where('id', $data['hotel_id'])
            ->orWhere(
                fn($q) =>
                $q->where('vendor', 'hotelbeds')
                    ->where('vendor_id', $data['hotel_id'])
            )
            ->first();

        if (! $hotel) {
            $hotel = $this->importSingle((int) $data['hotel_id'], $hb, $mediaService);
            if (! $hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not found and import failed',
                ], 404);
            }
        }

        if ($hotel->vendor !== 'hotelbeds') {
            return response()->json([
                'success' => false,
                'message' => 'Stripe checkout is only supported for Hotelbeds hotels.',
            ], 422);
        }

        /* ----------------------------------------------------
        * 2) Build Hotelbeds payload & PER-ROOM PRICING
        * ---------------------------------------------------- */
        $roomsHbPayload   = [];
        $allPaxes         = [];

        $totalVendorNet   = 0.0;
        $totalSelling     = 0.0;
        $totalMarkup      = 0.0;
        $pricingBreakdown = [];

        $currency = $data['currency'];
        $checkIn  = $data['check_in']  ?? null;
        $checkOut = $data['check_out'] ?? null;

        foreach ($data['rooms'] as $index => $roomReq) {
            $roomId = $index + 1;

            $paxesWithRoomId = [];
            foreach ($roomReq['paxes'] as $pax) {
                $paxesWithRoomId[] = [
                    'roomId'  => $roomId,
                    'type'    => $pax['type'],
                    'age'     => $pax['age'],
                    'name'    => $data['holder']['name'],
                    'surname' => $data['holder']['surname'],
                ];
            }

            $hbRoom = [
                'rateKey' => $roomReq['rate_key'],
                'paxes'   => $paxesWithRoomId,
            ];

            $netForRoom = null;

            // BOOKABLE
            if ($roomReq['rate_type'] === 'BOOKABLE') {
                if (!isset($roomReq['net'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'net is required for BOOKABLE rate_type',
                        'room'    => $index,
                    ], 422);
                }
                $netForRoom = (float) $roomReq['net'];
            }

            // RECHECK
            if ($roomReq['rate_type'] === 'RECHECK' || $netForRoom === null) {
                $check = $hb->checkRate(['rooms' => [$hbRoom]]);
                if (isset($check['error'])) {
                    return response()->json([
                        'success' => false,
                        'stage'   => 'check-rate',
                        'room'    => $index,
                        'error'   => $check['error'],
                    ], 400);
                }

                $rate = Arr::get($check, 'hotel.rooms.0.rates.0', []);
                $netForRoom = (float) ($rate['net'] ?? 0);

                if (!empty($rate['rateKey'])) {
                    $hbRoom['rateKey'] = $rate['rateKey'];
                    $data['rooms'][$index]['rate_key'] = $rate['rateKey'];
                }
            }

            /* ----------------------------------------------
            * ✅ APPLY PRICING PER ROOM (FIX)
            * ---------------------------------------------- */
            $pricing = PricingService::calculatePriceForLocation(
                vendorRate: $netForRoom,
                hotelMargin: null,
                country: $data['countryCode'],
                city: $data['cityCode'],
                context: []
            );

            $totalVendorNet += $pricing['vendor_net'];
            $totalSelling   += $pricing['final_price'];
            $totalMarkup    += ($pricing['final_price'] - $pricing['vendor_net']);

            $pricingBreakdown[] = [
                'rateKey'        => $hbRoom['rateKey'],
                'vendor_net'     => $pricing['vendor_net'],
                'selling_price'  => $pricing['selling_price'],
                'final_price'    => $pricing['final_price'],
                'margin_percent' => $pricing['margin_percent'],
                'scope_used'     => $pricing['scope_used'],
                'msp_scope'      => $pricing['msp_scope'],
            ];

            $roomsHbPayload[] = $hbRoom;
            $allPaxes = array_merge($allPaxes, $paxesWithRoomId);
        }

        /* ----------------------------------------------------
        * 3) FINAL TOTALS (✔ MSP SAFE)
        * ---------------------------------------------------- */
        $sellAmount   = round($totalSelling, 2);
        $netAmount    = round($totalVendorNet, 2);
        $markupAmount = round($totalMarkup, 2);

        /* ----------------------------------------------------
        * 4) Create Reservation
        * ---------------------------------------------------- */
        $clientRef = $data['client_reference'] ?? ('TRA-' . now()->format('YmdHis'));

        $reservation = Reservation::create([
            'hotel_id'        => $hotel->id,
            'guest_info'      => [
                'holder'             => $data['holder'],
                'rooms'              => $roomsHbPayload,
                'pricing_breakdown'  => $pricingBreakdown,
                'countryCode'        => $data['countryCode'],
                'cityCode'           => $data['cityCode'],
            ],
            'total_price'     => $sellAmount,
            'markup_amount'   => $markupAmount,
            'currency'        => $currency,
            'status'          => 'pending',
            'payment_status'  => 'pending_payment',
            'check_in'        => $checkIn,
            'check_out'       => $checkOut,
            'customer_name'   => $data['holder']['name'] . ' ' . $data['holder']['surname'],
            'customer_email'  => $data['customer_email'] ?? null,
            'booking_channel' => $data['channel'] ?? 'Website',
        ]);

        /* ----------------------------------------------------
        * 5) Stripe PaymentIntent
        * ---------------------------------------------------- */
        $intent = $stripe->createPaymentIntent(
            $sellAmount,
            $currency,
            ['reservation_id' => $reservation->id]
        );

        $reservation->stripe_payment_intent_id = $intent->id;
        $reservation->payment_status = $intent->status ?? 'pending_payment';
        $reservation->save();

        return response()->json([
            'success'       => true,
            'reservation_id' => $reservation->id,
            'amount'        => $sellAmount,
            'currency'      => $currency,
            'client_secret' => $intent->client_secret,
        ]);
    }


    /**
     * Import single hotel from Hotelbeds vendor into local DB.
     *
     * Returns Hotel model on success or throws RuntimeException on supplier error.
     */
    protected function importSingle(
        int $vendorId,
        HotelbedsService $hb,
        MediaService $mediaService
    ): ?Hotel {
        /*
     |------------------------------------------------------------
     | 1) AVAILABILITY (dynamic data: prices, rooms)
     |------------------------------------------------------------
     */
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
            'hotels' => [
                'hotel' => [(int) $vendorId],
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
            throw new \RuntimeException(
                $resp['error']['message'] ?? 'Supplier availability error'
            );
        }

        $h = data_get($resp, 'hotels.hotels.0');
        if (! $h) {
            throw new \RuntimeException('No hotel returned from supplier availability');
        }

        $name        = $h['name'] ?? $h['hotelName'] ?? 'Unnamed';
        $vendorCode  = $h['code'] ?? $vendorId;
        $firstRate   = data_get($h, 'rooms.0.rates.0');

        /*
     |------------------------------------------------------------
     | 2) CONTENT API (static hotel data)
     |------------------------------------------------------------
     */
        $content = $hb->getHotelContent($vendorCode);
        SupplierResponse::create([
            'supplier'        => 'hotelbeds',
            'endpoint'        => "/hotel-content-api/1.0/hotels/{$vendorCode}/details",
            'request_payload' => ['code' => $vendorCode],
            'response_body'   => json_encode($content),
            'status_code'     => isset($content['error']) ? 400 : 200,
        ]);

        $countryName      = data_get($content, 'hotel.country.description.content');
        $cityName         = data_get($content, 'hotel.city.content');
        $countryCode      = data_get($content, 'hotel.country.isoCode');
        $destinationCode  = data_get($content, 'hotel.destination.code');
        $destinationName  = data_get($content, 'hotel.destination.name.content');
        $longitude        = data_get($content, 'hotel.coordinates.longitude');
        $latitude         = data_get($content, 'hotel.coordinates.latitude');
        $hotelEmail       = data_get($content, 'hotel.email');
        $hotelPhones      = data_get($content, 'hotel.phones');
        $address          = data_get($content, 'hotel.address.content');
        $currency          = data_get($content, 'hotel.currency');

        /*
     |------------------------------------------------------------
     | 3) SAVE / UPDATE HOTEL
     |------------------------------------------------------------
     */
        $hotel = Hotel::updateOrCreate(
            ['vendor' => 'hotelbeds', 'vendor_id' => $vendorCode],
            [
                'name'              => $name,
                'slug'              => Str::slug($name . '-' . $vendorCode),
                'country'           => $countryName,
                'city'              => $cityName,
                'country_iso'       => $countryCode,
                'destination_code'  => $destinationCode,
                'destination_name'  => $destinationName,
                'longitude'         => $longitude,
                'latitude'          => $latitude,
                'address'           => $address,
                'hotel_email'       => $hotelEmail,
                'hotel_phones'      => $hotelPhones,
                'lowest_rate'       => $firstRate['net'] ?? $firstRate['price'] ?? null,
                'currency'          => $currency ?? null,
                'meta'              => $h,
                'status'            => 'active',
            ]
        );

        /*
     |------------------------------------------------------------
     | 4) DESCRIPTION
     |------------------------------------------------------------
     */
        $description = data_get($content, 'hotel.description.content')
            ?? data_get($content, 'hotel.description');

        if ($description && empty($hotel->description)) {
            $hotel->update(['description' => $description]);
        }

        /*
     |------------------------------------------------------------
     | 5) IMAGES
     |------------------------------------------------------------
     */
        $images = data_get($content, 'hotel.images', []);

        foreach ($images as $img) {
            $rawPath = $img['path'] ?? $img['imageUrl'] ?? null;
            if (! $rawPath) {
                continue;
            }

            $url = Str::startsWith($rawPath, ['http://', 'https://'])
                ? $rawPath
                : 'https://photos.hotelbeds.com/giata/' . ltrim($rawPath, '/');

            if ($hotel->media()->where('external_url', $url)->exists()) {
                continue;
            }

            try {
                $mediaService->importForHotel($hotel->id, $url, 'images', [
                    'source'               => 'hotelbeds-content',
                    'type'                 => $img['type'] ?? null,
                    'order'                => $img['order'] ?? null,
                    'visualOrder'          => $img['visualOrder'] ?? null,
                    'roomCode'             => $img['roomCode'] ?? null,
                    'characteristicCode'   => $img['characteristicCode'] ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::warning(
                    "Failed to import image for hotel {$hotel->id}",
                    ['url' => $url, 'error' => $e->getMessage()]
                );
            }
        }

        return $hotel->fresh()->load('media');
    }

    /**
     * Direct booking (without Stripe)
     *
     * POST /api/reservations
     */
    public function store(Request $req, HotelbedsService $hb)
    {
        $data = $req->validate([
            'hotel_id'          => 'required|integer',
            'room_id'           => 'nullable|string',

            // multi-room
            'rooms'                 => 'sometimes|array|min:1',
            'rooms.*.room_id'       => 'nullable|string',
            'rooms.*.rate_key'      => 'required_with:rooms|string',
            'rooms.*.paxes'         => 'required_with:rooms|array|min:1',
            'rooms.*.paxes.*.type'  => 'in:AD,CH',
            'rooms.*.paxes.*.age'   => 'integer|min:0',

            // single-room
            'rate_key'          => 'sometimes|string',
            'paxes'             => 'sometimes|array',
            'paxes.*.type'      => 'in:AD,CH',
            'paxes.*.age'       => 'integer|min:0',

            'holder.name'       => 'required|string',
            'holder.surname'    => 'required|string',

            'client_reference'  => 'nullable|string',
            'remark'            => 'nullable|string',
            'channel'           => 'nullable|string|max:50',

            // local-only
            'guest'             => 'nullable|array',
            'billing'           => 'nullable|array',
            'total_price'       => 'nullable|numeric',
        ]);

        // Find hotel: either local id or Hotelbeds code
        $hotel = Hotel::where('id', $data['hotel_id'])
            ->orWhere(function ($q) use ($data) {
                $q->where('vendor', 'hotelbeds')
                    ->where('vendor_id', $data['hotel_id']);
            })
            ->firstOrFail();

        // HOTELBEDS FLOW (direct booking, no Stripe)
        if ($hotel->vendor === 'hotelbeds' && (!empty($data['rate_key']) || !empty($data['rooms']))) {

            $roomsPayload = [];
            $allPaxes     = [];

            if (!empty($data['rooms'])) {
                // Multi-room
                foreach ($data['rooms'] as $index => $roomReq) {
                    $roomId = $index + 1;
                    $paxesWithRoomId = [];

                    foreach ($roomReq['paxes'] as $pax) {
                        $paxesWithRoomId[] = [
                            'roomId'  => $roomId,
                            'type'    => $pax['type'],
                            'age'     => $pax['age'],
                            'name'    => $data['holder']['name'],
                            'surname' => $data['holder']['surname'],
                        ];
                    }

                    $roomsPayload[] = [
                        'rateKey' => $roomReq['rate_key'],
                        'paxes'   => $paxesWithRoomId,
                    ];

                    $allPaxes = array_merge($allPaxes, $paxesWithRoomId);
                }
            } else {
                // Single-room
                if (empty($data['paxes'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'paxes are required for Hotelbeds booking',
                    ], 422);
                }

                $paxesWithRoomId = [];
                foreach ($data['paxes'] as $pax) {
                    $paxesWithRoomId[] = [
                        'roomId'  => 1,
                        'type'    => $pax['type'],
                        'age'     => $pax['age'],
                        'name'    => $data['holder']['name'],
                        'surname' => $data['holder']['surname'],
                    ];
                }

                $roomsPayload[] = [
                    'rateKey' => $data['rate_key'],
                    'paxes'   => $paxesWithRoomId,
                ];

                $allPaxes = $paxesWithRoomId;
            }

            if (empty($data['holder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'holder is required for Hotelbeds booking',
                ], 422);
            }

            // 1a) BEST PRACTICE: CheckRates per room (one rateKey per call)
            foreach ($roomsPayload as $i => $room) {
                $singleCheckPayload = [
                    'rooms' => [
                        [
                            'rateKey' => $room['rateKey'],
                        ],
                    ],
                ];

                $check = $hb->checkRate($singleCheckPayload);

                SupplierResponse::create([
                    'supplier'        => 'hotelbeds',
                    'endpoint'        => '/hotel-api/1.0/checkrates',
                    'request_payload' => $singleCheckPayload,
                    'response_body'   => json_encode($check),
                    'status_code'     => isset($check['error']) ? 400 : 200,
                ]);

                if (isset($check['error'])) {
                    return response()->json([
                        'success' => false,
                        'stage'   => 'check-rate',
                        'room'    => $i,
                        'error'   => $check['error'],
                        'raw'     => $check['raw'] ?? null,
                    ], 400);
                }

                $updatedRoom = Arr::get($check, 'hotel.rooms.0', []);
                $updatedRate = Arr::get($updatedRoom, 'rates.0', []);

                if (!empty($updatedRate['rateKey'])) {
                    $newRateKey = $updatedRate['rateKey'];

                    $roomsPayload[$i]['rateKey'] = $newRateKey;

                    if (!empty($data['rate_key']) && $i === 0) {
                        $data['rate_key'] = $newRateKey;
                    }

                    if (!empty($data['rooms'][$i]['rate_key'])) {
                        $data['rooms'][$i]['rate_key'] = $newRateKey;
                    }
                }
            }

            // 1b) Build booking payload
            $clientRef = $data['client_reference'] ?? ('TRAVACOT-' . now()->format('YmdHis'));

            $bookingPayload = [
                'holder' => [
                    'name'    => $data['holder']['name'],
                    'surname' => $data['holder']['surname'],
                ],
                'rooms'           => $roomsPayload,
                'clientReference' => $clientRef,
            ];

            if (!empty($data['remark'])) {
                $bookingPayload['remark'] = $data['remark'];
            }

            // 1c) Call /bookings
            $resp = $hb->book($bookingPayload);

            SupplierResponse::create([
                'supplier'        => 'hotelbeds',
                'endpoint'        => '/hotel-api/1.0/bookings',
                'request_payload' => $bookingPayload,
                'response_body'   => json_encode($resp),
                'status_code'     => isset($resp['error']) ? 400 : 200,
            ]);

            if (isset($resp['error'])) {
                return response()->json([
                    'success' => false,
                    'stage'   => 'book',
                    'error'   => $resp['error'],
                    'raw'     => $resp['raw'] ?? null,
                ], 400);
            }

            $booking = Arr::get($resp, 'booking');
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected supplier response (no booking object)',
                    'raw'     => $resp,
                ], 500);
            }
            
            // 1d) Map to local reservation
            $checkIn  = Arr::get($booking, 'hotel.checkIn');
            $checkOut = Arr::get($booking, 'hotel.checkOut');

            $totalNet = Arr::get($booking, 'hotel.totalNet')
                ?? Arr::get($booking, 'totalNet');

            $currency = Arr::get($booking, 'hotel.currency')
                ?? Arr::get($booking, 'currency');

            $statusFromSupplier = strtoupper(Arr::get($booking, 'status', 'CONFIRMED'));
            $localStatus = match ($statusFromSupplier) {
                'CONFIRMED' => 'confirmed',
                'CANCELLED' => 'cancelled',
                default     => 'pending',
            };

            $adults   = collect($allPaxes)->where('type', 'AD')->count();
            $children = collect($allPaxes)->where('type', 'CH')->count();

            $guestInfo = [
                'holder'           => $booking['holder'] ?? $data['holder'],
                'rooms'            => $roomsPayload,
                'client_reference' => $clientRef,
                'supplier_raw'     => $booking,
            ];

            $reservation = null;

            DB::transaction(function () use (
                &$reservation,
                $hotel,
                $booking,
                $checkIn,
                $checkOut,
                $totalNet,
                $currency,
                $localStatus,
                $guestInfo,
                $adults,
                $children,
                $data
            ) {
                $reservation = Reservation::create([
                    'confirmation_number' => Arr::get($booking, 'reference'),
                    'hotel_id'            => $hotel->id,
                    'room_id'             => null,

                    'guest_info'          => $guestInfo,
                    'total_price'         => $totalNet ?? 0,
                    'currency'            => $currency,
                    'status'              => $localStatus,
                    'payment_status'      => 'succeeded', // direct flow: assume paid
                    'raw_response'        => $booking,

                    'check_in'            => $checkIn,
                    'check_out'           => $checkOut,
                    'adults'              => $adults,
                    'children'            => $children,
                    'customer_name'       => $guestInfo['holder']['name'] . ' ' . $guestInfo['holder']['surname'],
                    'customer_email'      => null,
                    'booking_channel'     => $data['channel'] ?? 'Website',
                    'supplier_reference'  => Arr::get($booking, 'reference'),
                ]);
            });

            return response()->json([
                'success'     => true,
                'reservation' => $reservation,
                'supplier'    => $booking,
            ]);
        }

        // LOCAL-ONLY BOOKING (non-Hotelbeds)
        $guestInfo = $data['guest'] ?? [];
        $total     = $data['total_price'] ?? 0;
        $currency  = $hotel->currency ?? 'USD';

        $reservation = Reservation::create([
            'confirmation_number' => 'LOCAL-' . strtoupper(uniqid()),
            'hotel_id'            => $hotel->id,
            'room_id'             => null,
            'guest_info'          => $guestInfo,
            'total_price'         => $total,
            'currency'            => $currency,
            'status'              => 'confirmed',
            'payment_status'      => 'succeeded',
            'raw_response'        => null,

            'check_in'            => $req->input('check_in'),
            'check_out'           => $req->input('check_out'),
            'adults'              => $req->input('adults', 2),
            'children'            => $req->input('children', 0),
            'customer_name'       => Arr::get($guestInfo, 'name'),
            'customer_email'      => Arr::get($guestInfo, 'email'),
            'booking_channel'     => $req->input('channel', 'Website'),
            'supplier_reference'  => null,
        ]);

        return response()->json([
            'success'     => true,
            'reservation' => $reservation,
        ]);
    }

    /**
     * Cancel a reservation.
     *
     * If the reservation is a Hotelbeds booking (supplier_reference present),
     * call Hotelbeds BookingCancellation, then mark local reservation as cancelled.
     *
     * Route: DELETE /api/reservations/{reservation}
     */
    public function destroy(Reservation $reservation, HotelbedsService $hb)
    {
        // Default response structure
        $result = [
            'success' => true,
            'hotelbeds' => null,
        ];

        // If we have a Hotelbeds reference, cancel remotely as well
        $supplierRef = $reservation->supplier_reference;

        if ($supplierRef) {
            $hbResp = $hb->cancelBooking($supplierRef, 'CANCELLATION', 'ENG');

            // Store supplier response for debugging/auditing
            SupplierResponse::create([
                'supplier'        => 'hotelbeds',
                'endpoint'        => '/hotel-api/1.0/bookings/' . $supplierRef,
                'request_payload' => ['cancellationFlag' => 'CANCELLATION', 'language' => 'ENG'],
                'response_body'   => json_encode($hbResp),
                'status_code'     => isset($hbResp['error']) ? 400 : 200,
            ]);

            if (isset($hbResp['error'])) {
                // Do NOT auto-cancel locally if supplier cancellation failed
                return response()->json([
                    'success' => false,
                    'stage'   => 'supplier-cancel',
                    'error'   => $hbResp['error'],
                    'raw'     => $hbResp['raw'] ?? null,
                ], 400);
            }

            $result['hotelbeds'] = $hbResp;
        }

        // Local soft-cancel
        $reservation->update(['status' => 'cancelled']);

        return response()->json($result);
    }

    /**
     * Hotelbeds-style CheckRate endpoint alias.
     * POST /api/hotelbeds/checkrate
     *
     * Payload:
     * {
     *   "rate_key": "...."                // OR
     *   "rate_keys": ["...", "..."]
     * }
     *
     * Response: same as preview()
     */
    public function hotelbedsCheckRate(Request $request, HotelbedsService $hb)
    {
        // Simply reuse the existing preview() logic
        return $this->preview($request, $hb);
    }

    /**
     * JSON Voucher endpoint for frontend.
     *
     * GET /api/reservations/{reservation}/voucher
     *
     * Protected by auth:sanctum.
     */
    public function voucher(Reservation $reservation)
    {
        $reservation->load('hotel');

        $hotel = $reservation->hotel;
        $guestInfo = $reservation->guest_info ?? [];

        // Flatten paxes from guest_info->rooms[*].paxes
        $paxes = [];
        foreach ($guestInfo['rooms'] ?? [] as $room) {
            foreach ($room['paxes'] ?? [] as $p) {
                $paxes[] = $p;
            }
        }

        $holder = $guestInfo['holder'] ?? [
            'name'    => null,
            'surname' => null,
        ];

        $voucher = [
            'reservation_id'      => $reservation->id,
            'confirmation_number' => $reservation->confirmation_number,
            'supplier_reference'  => $reservation->supplier_reference,
            'status'              => $reservation->status,
            'currency'            => $reservation->currency,
            'total_price'         => $reservation->total_price,

            'hotel' => [
                'id'              => $hotel?->id,
                'vendor'          => $hotel?->vendor,
                'vendor_id'       => $hotel?->vendor_id,
                'name'            => $hotel?->name,
                'category'        => $hotel?->category,
                'address'         => $hotel?->address,
                'city'            => $hotel?->city,
                'country'         => $hotel?->country,
                'phone'           => $hotel?->phone,
                'destinationCode' => data_get($guestInfo, 'cityCode'),
                'countryCode'     => data_get($guestInfo, 'countryCode'),
            ],

            'stay' => [
                'check_in'  => $reservation->check_in,
                'check_out' => $reservation->check_out,
                'nights'    => $reservation->check_in && $reservation->check_out
                    ? Carbon::parse($reservation->check_in)
                    ->diffInDays(Carbon::parse($reservation->check_out))
                    : null,
            ],

            'holder' => [
                'name'    => $holder['name']    ?? null,
                'surname' => $holder['surname'] ?? null,
            ],

            'paxes' => $paxes,

            'client_reference' => $guestInfo['client_reference'] ?? null,
            'remark'           => $guestInfo['remark'] ?? null,

            // Optional raw Hotelbeds booking if we have it
            'supplier_raw'     => $guestInfo['supplier_raw'] ?? null,
        ];

        return response()->json([
            'success' => true,
            'voucher' => $voucher,
        ]);
    }
}
