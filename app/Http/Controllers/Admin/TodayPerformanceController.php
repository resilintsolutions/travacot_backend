<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\SupplierResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class TodayPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $today      = now()->startOfDay();
        $todayEnd   = now()->endOfDay();
        $yesterday  = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        /*
         * 1) UPCOMING STAYS / CHECK-INS TODAY
         * For now we treat reservations created today as "today's stays".
         * Later, when you add check_in/check_out columns, just switch to those.
         */
        $upcomingReservations = Reservation::with('hotel')
            ->whereDate('check_in', '>=', $today)
            ->whereDate('check_in', '<=', $todayEnd)
            ->orderBy('check_in')
            ->limit(8)
            ->get();

        $upcomingStays = $upcomingReservations->map(function (Reservation $r) {
            $hotel = $r->hotel;
            $guestInfo = is_array($r->guest_info)
                ? $r->guest_info
                : (json_decode($r->guest_info ?? '', true) ?: []);

            $guestName = $r->customer_name
                ?? $guestInfo['name']
                ?? data_get($guestInfo, 'primary.name')
                ?? data_get($guestInfo, 'primary_guest')
                ?? 'Guest';

            return [
                'guest_name'   => $guestName,
                'hotel_name'   => $hotel?->name ?? '—',
                'destination'  => trim(($hotel->country ?? '') . ', ' . ($hotel->city ?? '')),
                'confirmation' => $r->confirmation_number ?? '—',
                'check_in'     => $r->created_at?->format('d/m/Y'),
                // temporary: +1 day as check-out. Replace with real column later.
                'check_out'    => $r->created_at?->copy()->addDay()->format('d/m/Y'),
                'channel'      => data_get($guestInfo, 'channel', 'Website'),
                'customer_paid'=> $r->total_price,
                'currency'     => $r->currency ?? 'USD',
                'status'       => $r->status,
                'api_call'     => $r->status === 'failed' ? 'Failed' : 'Success',
            ];
        });

        /*
         * 2) SEARCH ACTIVITY – based on Hotelbeds availability calls.
         * We treat supplier_responses to /hotel-api/1.0/hotels as "searches".
         */
        $searchQuery = SupplierResponse::whereBetween('created_at', [$today, $todayEnd])
            ->where('endpoint', '/hotel-api/1.0/hotels');

        $totalSearches = (clone $searchQuery)->count();

        $searchesNoResults = (clone $searchQuery)
            ->where(function ($q) {
                $q->where('response_body', 'like', '%"hotels":[]%')
                  ->orWhere('response_body', 'like', '%"hotels":{"hotels":[]}%');
            })
            ->count();

        // "High response times" we approximate as 5xx errors (server issues)
        $searchesHighResponse = (clone $searchQuery)
            ->where('status_code', '>=', 500)
            ->count();

        $searchActivity = [
            'no_results'       => $searchesNoResults,
            'high_response'    => $searchesHighResponse,
            'total_searches'   => $totalSearches,
        ];

        /*
         * 3) TOP PERFORMING SUPPLIER
         * - Most bookings: supplier with most confirmed reservations today
         * - Highest conversion: (bookings today / searches today) per supplier
         */
        $bookingsBySupplier = Reservation::whereBetween('reservations.created_at', [$today, $todayEnd])
            ->where('reservations.status', 'confirmed')
            ->join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
            ->select('hotels.vendor as supplier', DB::raw('COUNT(*) as total'))
            ->whereNotNull('hotels.vendor')
            ->groupBy('hotels.vendor')
            ->pluck('total', 'supplier');

        $searchesBySupplier = SupplierResponse::whereBetween('created_at', [$today, $todayEnd])
            ->where('endpoint', '/hotel-api/1.0/hotels')
            ->select('supplier', DB::raw('COUNT(*) as total'))
            ->groupBy('supplier')
            ->pluck('total', 'supplier');

        $mostBookingsSupplier = $bookingsBySupplier->sortDesc()->keys()->first();

        $conversionBySupplier = collect();
        foreach ($searchesBySupplier as $supplier => $searchCount) {
            $bookings = $bookingsBySupplier[$supplier] ?? 0;
            if ($searchCount > 0) {
                $conversionBySupplier[$supplier] = $bookings / $searchCount;
            }
        }
        $highestConversionSupplier = $conversionBySupplier->sortDesc()->keys()->first();

        $topSupplierStatus = 'No data';
        if ($mostBookingsSupplier && isset($conversionBySupplier[$mostBookingsSupplier])) {
            $conv = $conversionBySupplier[$mostBookingsSupplier];
            if ($conv >= 0.2)      $topSupplierStatus = 'High';
            elseif ($conv >= 0.05) $topSupplierStatus = 'Medium';
            else                   $topSupplierStatus = 'Low';
        }

        $topSupplier = [
            'most_bookings'      => $mostBookingsSupplier,
            'highest_conversion' => $highestConversionSupplier,
            'status'             => $topSupplierStatus,
        ];

        /*
         * 4) API ERRORS TODAY – from supplier_responses
         */
        $topErrors = SupplierResponse::whereBetween('created_at', [$today, $todayEnd])
            ->where('status_code', '>=', 400)
            ->select('supplier', DB::raw('COUNT(*) as total'))
            ->groupBy('supplier')
            ->orderByDesc('total')
            ->first();

        $apiErrorsToday = [
            'supplier'   => $topErrors->supplier ?? null,
            'error_count'=> $topErrors->total ?? 0,
            'severity'   => ($topErrors && $topErrors->total > 10) ? 'High' : (($topErrors && $topErrors->total > 0) ? 'Medium' : 'None'),
        ];

        /*
         * 5) BOOKING SUMMARY (today vs yesterday)
         */
        $summaryToday = Reservation::whereBetween('reservations.created_at', [$today, $todayEnd])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $summaryYesterday = Reservation::whereBetween('reservations.created_at', [$yesterday, $yesterdayEnd])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $bookingSummary = [
            'success_today'   => $summaryToday['confirmed'] ?? 0,
            'failed_today'    => $summaryToday['failed'] ?? 0,
            'cancelled_today' => $summaryToday['cancelled'] ?? 0,

            'success_diff'   => ($summaryToday['confirmed'] ?? 0) - ($summaryYesterday['confirmed'] ?? 0),
            'failed_diff'    => ($summaryToday['failed'] ?? 0) - ($summaryYesterday['failed'] ?? 0),
            'cancelled_diff' => ($summaryToday['cancelled'] ?? 0) - ($summaryYesterday['cancelled'] ?? 0),
        ];

        /*
         * 6) REVENUE (today vs yesterday) – confirmed reservations only
         */
        $revToday = Reservation::whereBetween('reservations.created_at', [$today, $todayEnd])
            ->where('status', 'confirmed')
            ->select(
                DB::raw('SUM(total_price) as total'),
                DB::raw('AVG(total_price) as avg')
            )
            ->first();

        $revYesterday = Reservation::whereBetween('reservations.created_at', [$yesterday, $yesterdayEnd])
            ->where('status', 'confirmed')
            ->select(
                DB::raw('SUM(total_price) as total'),
                DB::raw('AVG(total_price) as avg')
            )
            ->first();

        $revenue = [
            'total_today' => (float) ($revToday->total ?? 0),
            'avg_today'   => (float) ($revToday->avg ?? 0),
            'total_diff'  => (float) ($revToday->total ?? 0) - (float) ($revYesterday->total ?? 0),
            'avg_diff'    => (float) ($revToday->avg ?? 0) - (float) ($revYesterday->avg ?? 0),
        ];

        return view('admin.today_performance.index', [
            'upcomingStays'   => $upcomingStays,
            'searchActivity'  => $searchActivity,
            'topSupplier'     => $topSupplier,
            'apiErrorsToday'  => $apiErrorsToday,
            'bookingSummary'  => $bookingSummary,
            'revenue'         => $revenue,
        ]);
    }
}
