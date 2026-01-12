<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\SupplierResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiStatusController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        // 1) Get REAL suppliers from supplier_responses and/or hotels
        $supplierCodes = SupplierResponse::query()
            ->select('supplier')
            ->distinct()
            ->when($search, function ($q) use ($search) {
                $q->where('supplier', 'like', "%{$search}%");
            })
            ->pluck('supplier');

        // also include any vendors that exist in hotels but never logged yet
        $vendorCodes = Hotel::query()
            ->select('vendor')
            ->whereNotNull('vendor')
            ->when($search, function ($q) use ($search) {
                $q->where('vendor', 'like', "%{$search}%");
            })
            ->distinct()
            ->pluck('vendor');

        $allCodes = $supplierCodes
            ->merge($vendorCodes)
            ->unique()
            ->values();

        $rows = $allCodes->map(function (string $code) {

            // ---------- INVENTORY METRICS (REAL) ----------
            $hotelIds = Hotel::where('vendor', $code)->pluck('id');

            $activeHotels = Hotel::where('vendor', $code)
                ->where('status', 'active')
                ->count();

            $inactiveHotels = Hotel::where('vendor', $code)
                ->where('status', 'inactive')
                ->count();

            $totalInventory = $hotelIds->count();

            // “Funneled Hotels” = hotels that actually received at least one reservation
            $funneledHotels = Reservation::whereIn('hotel_id', $hotelIds)
                ->distinct('hotel_id')
                ->count('hotel_id');

            // ---------- DEMAND METRICS (REAL, IF DATA EXISTS) ----------

            // Top demand room (by count of reservations per room)
            $topDemandRoom = null;
            if (Schema::hasTable('rooms')) {
                $topDemandRoom = Reservation::whereIn('reservations.hotel_id', $hotelIds)
                    ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
                    ->select('rooms.name', DB::raw('COUNT(*) as total'))
                    ->groupBy('rooms.name')
                    ->orderByDesc('total')
                    ->value('rooms.name');
            }

            // Most booked FROM: guest country from JSON (guest_info.country)
            $mostBookedFrom = Reservation::whereIn('hotel_id', $hotelIds)
                ->select(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(guest_info, '$.country')) as country"),
                    DB::raw('COUNT(*) as total')
                )
                ->whereNotNull(DB::raw("JSON_EXTRACT(guest_info, '$.country')"))
                ->groupBy('country')
                ->orderByDesc('total')
                ->value('country');

            // Most booked TO: hotel city
            $mostBookedTo = Reservation::whereIn('reservations.hotel_id', $hotelIds)
                ->join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
                ->select('hotels.city', DB::raw('COUNT(*) as total'))
                ->groupBy('hotels.city')
                ->orderByDesc('total')
                ->value('city');

            // ---------- API HEALTH (REAL) ----------

            $lastResponse = SupplierResponse::where('supplier', $code)
                ->latest('created_at')
                ->first();

            $apiStatus = 'Unknown';
            $apiStatusCode = null;

            if ($lastResponse) {
                $apiStatusCode = $lastResponse->status_code;

                if ($apiStatusCode >= 200 && $apiStatusCode < 300) {
                    $apiStatus = 'Active';
                } else {
                    $apiStatus = 'Inactive';
                }
            }

            return [
                'partner'          => ucfirst($code),
                'code'             => $code,
                'active_hotels'    => $activeHotels,
                'inactive_hotels'  => $inactiveHotels,
                'funneled_hotels'  => $funneledHotels,
                'total_inventory'  => $totalInventory,
                'top_demand_room'  => $topDemandRoom,
                'most_booked_from' => $mostBookedFrom,
                'most_booked_to'   => $mostBookedTo,
                'api_status'       => $apiStatus,
                'api_status_code'  => $apiStatusCode,
            ];
        });

        // If you want JSON for your frontend
        // return response()->json([
        //     'success'   => true,
        //     'suppliers' => $rows,
        // ]);

        // If you want Blade instead, you can do:
         return view('admin.api_status.index', ['rows' => $rows, 'search' => $search]);
    }
}
