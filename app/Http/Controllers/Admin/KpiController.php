<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        // period filter: today | week | month
        $period = $request->get('period', 'today');

        switch ($period) {
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $to   = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth();
                $to   = Carbon::now()->endOfMonth();
                break;
            default: // today
                $from = Carbon::now()->startOfDay();
                $to   = Carbon::now()->endOfDay();
        }

        // base reservation query for the selected period
        // IMPORTANT: qualify created_at with table name to avoid ambiguity after joins
        $reservationBase = Reservation::whereBetween('reservations.created_at', [$from, $to]);

        // ---------- CORE BOOKING METRICS ----------

        // 1) Total confirmed reservations (treat as "successful bookings")
        $totalBookings = (clone $reservationBase)
            ->where('reservations.status', 'confirmed')
            ->count();

        // 2) Revenue after markups = total_price + markup_amount
        $hasMarkup = Schema::hasColumn('reservations', 'markup_amount');

        $revenueExpr = $hasMarkup
            ? 'SUM(total_price + COALESCE(markup_amount,0)) as total'
            : 'SUM(total_price) as total';

        $revenue = (clone $reservationBase)
            ->where('reservations.status', 'confirmed')
            ->selectRaw($revenueExpr)
            ->value('total') ?? 0;

        // 3) Average booking value
        $avgBookingValue = $totalBookings
            ? $revenue / $totalBookings
            : 0;

        // 4) Booking attempts (all reservations in period)
        $totalBookingAttempts = (clone $reservationBase)->count();

        // 5) Cancellation count & rate
        $cancelledCount = (clone $reservationBase)
            ->where('reservations.status', 'cancelled')
            ->count();

        $cancellationRate = $totalBookingAttempts
            ? round($cancelledCount / $totalBookingAttempts * 100, 1)
            : 0;

        // 6) "Refund queue" – for now, treat cancelled as needing refund (you can change later)
        $refundPending = $cancelledCount;

        // ---------- CHANNEL / DEVICE METRICS ----------

        $mobilePct = 0;
        $webPct    = 0;

        if (Schema::hasColumn('reservations', 'channel')) {
            $channelCounts = (clone $reservationBase)
                ->select('channel', DB::raw('COUNT(*) as c'))
                ->groupBy('channel')
                ->pluck('c', 'channel');

            $mobileBookings = $channelCounts['mobile'] ?? 0;
            $webBookings    = $channelCounts['web'] ?? 0;
            $totalChannel   = $mobileBookings + $webBookings;

            $mobilePct = $totalChannel ? round($mobileBookings / $totalChannel * 100) : 0;
            $webPct    = $totalChannel ? round($webBookings / $totalChannel * 100) : 0;
        }

        // ---------- USER-BASED METRICS (optional, only if user_id exists) ----------

        $newPct      = 0;
        $retPct      = 0;
        $activeUsers = 0;

        if (Schema::hasColumn('reservations', 'user_id')) {
            // active users = distinct users with reservations in this period
            $activeUsers = (clone $reservationBase)
                ->whereNotNull('reservations.user_id')
                ->distinct('user_id')
                ->count('user_id');

            // new vs returning users – based on lifetime reservation count
            $userCounts = Reservation::select('user_id', DB::raw('COUNT(*) as c'))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->get();

            $newUserIds       = $userCounts->where('c', 1)->pluck('user_id')->toArray();
            $returningUserIds = $userCounts->where('c', '>', 1)->pluck('user_id')->toArray();

            $newBookingsThisPeriod = (clone $reservationBase)
                ->whereIn('user_id', $newUserIds)
                ->count();

            $returningBookingsThisPeriod = (clone $reservationBase)
                ->whereIn('user_id', $returningUserIds)
                ->count();

            $nrTotal = $newBookingsThisPeriod + $returningBookingsThisPeriod;

            $newPct = $nrTotal ? round($newBookingsThisPeriod / $nrTotal * 100) : 0;
            $retPct = $nrTotal ? round($returningBookingsThisPeriod / $nrTotal * 100) : 0;
        }

        // ---------- STAY / NIGHTS METRICS ----------

        $avgNights = 0;

        if (Schema::hasColumn('reservations', 'check_in') && Schema::hasColumn('reservations', 'check_out')) {
            $avgNights = (clone $reservationBase)
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->selectRaw('AVG(DATEDIFF(check_out, check_in)) as avg_nights')
                ->value('avg_nights') ?? 0;
        }

        // ---------- SEARCH / CONVERSION (if search_logs table exists) ----------

        $searchVolume       = 0;
        $conversionRate     = 0;
        $bookingSuccessRate = 0;

        if (Schema::hasTable('search_logs')) {
            // also qualify created_at here for future safety
            $searchBase = SearchLog::whereBetween('search_logs.created_at', [$from, $to]);

            $searchVolume = (clone $searchBase)->count();

            $conversionRate = $searchVolume
                ? round($totalBookings / $searchVolume * 100, 1)
                : 0;
        }

        // Booking success rate = confirmed / booking attempts
        $bookingSuccessRate = $totalBookingAttempts
            ? round($totalBookings / $totalBookingAttempts * 100, 1)
            : 0;

        // ---------- TOP DESTINATIONS (by hotel country) ----------

        $topDestinations = (clone $reservationBase)
            ->where('reservations.status', 'confirmed')
            ->join('hotels', 'reservations.hotel_id', '=', 'hotels.id')
            ->select(
                'hotels.country',
                DB::raw('COUNT(*) as total_booked')
            )
            ->groupBy('hotels.country')
            ->orderByDesc('total_booked')
            ->limit(5)
            ->get();

        return view('admin.dashboard.kpis', [
            'period'             => $period,
            'from'               => $from,
            'to'                 => $to,
            'totalBookings'      => $totalBookings,
            'revenue'            => $revenue,
            'avgBookingValue'    => $avgBookingValue,
            'refundPending'      => $refundPending,
            'mobilePct'          => $mobilePct,
            'webPct'             => $webPct,
            'newPct'             => $newPct,
            'retPct'             => $retPct,
            'activeUsers'        => $activeUsers,
            'avgNights'          => $avgNights,
            'searchVolume'       => $searchVolume,
            'conversionRate'     => $conversionRate,
            'bookingSuccessRate' => $bookingSuccessRate,
            'cancellationRate'   => $cancellationRate,
            'topDestinations'    => $topDestinations,
        ]);
    }
}
