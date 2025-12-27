<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpmController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();

        /* -----------------------------------------------------------
         * BOOKINGS IN LAST 24 HOURS
         * ----------------------------------------------------------- */
        $bookings24Sub = Reservation::selectRaw('hotel_id, COUNT(*) AS bookings_24h')
            ->where('status', 'confirmed')
            ->whereBetween('created_at', [$now->copy()->subDay(), $now])
            ->groupBy('hotel_id');

        /* -----------------------------------------------------------
         * BASE HOTEL QUERY
         * ----------------------------------------------------------- */
        $query = Hotel::query()
            ->leftJoinSub($bookings24Sub, 'b24', 'b24.hotel_id', '=', 'hotels.id')
            ->select(
                'hotels.*',
                DB::raw('COALESCE(b24.bookings_24h, 0) AS bookings_24h')
            );

        /* -----------------------------------------------------------
         * SEARCH FILTER
         * ----------------------------------------------------------- */
        if ($search = $request->q) {
            $query->where('hotels.name', 'like', "%{$search}%");
        }

        /* COUNTRY FILTER (optional for PPM page) */
        if ($country = $request->country) {
            $query->where('hotels.country_iso', $country);
        }

        $hotels = $query->orderBy('hotels.name')->get();

        /* -----------------------------------------------------------
         * APPLY PRICING ENGINE PER HOTEL
         * ----------------------------------------------------------- */
        $hotels->transform(function ($hotel) {

            $calc = PricingService::calculatePriceForLocation(
                vendorRate:    $hotel->lowest_rate ?? 0,
                hotelMargin:   $hotel->margin_inc,               // your field for hotel override
                country:       $hotel->country_iso,              // correct country match
                city:          $hotel->destination_code,         // correct city match
                context: [
                    'bookings_24h'            => $hotel->bookings_24h,
                    'market_rate'             => $hotel->market_rate ?? null,
                    'competitor_rate'         => $hotel->competitor_rate ?? null,
                    'conversion_rate_percent' => $hotel->conversion_rate ?? null,
                ]
            );

            // For UI
            $hotel->vendor_net           = $calc['vendor_net'];
            $hotel->effective_min_price  = $calc['effective_min'];
            $hotel->final_margin_percent = $calc['margin_percent'];
            $hotel->rate_in_market       = $calc['selling_price'];
            $hotel->msp_scope            = $calc['msp_scope'];
            $hotel->rules_scope          = $calc['scope_used'] ?? null;
            $hotel->margin_source        = $calc['margin_source'];


            return $hotel;
        });

        /* -----------------------------------------------------------
         * COUNTRY DROPDOWN LIST
         * ----------------------------------------------------------- */
        $countries = Hotel::select('country_iso')
            ->distinct()
            ->orderBy('country_iso')
            ->pluck('country_iso');

        return view('admin.ppm.index', [
            'hotels'    => $hotels,
            'countries' => $countries,
            'filters'   => [
                'q'       => $request->get('q'),
                'country' => $request->get('country'),
            ],
        ]);
    }
}
