<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function kpis(Request $req)
    {
        $range = $req->query('range', 'week');
        $from = match($range) {
            'day' => now()->subDay(),
            'month' => now()->subMonth(),
            default => now()->subWeek(),
        };
        $totalBookings = Reservation::where('created_at', '>=', $from)->count();
        $totalRevenue = Reservation::where('created_at', '>=', $from)->sum('total_price');
        $avgBooking = $totalBookings ? ($totalRevenue / $totalBookings) : 0;
        return response()->json([
            'totalBookings' => $totalBookings,
            'totalRevenue' => (float) $totalRevenue,
            'averageBookingValue' => (float) $avgBooking,
            'averageNights' => 3,
            'conversionRate' => 1.5,
            'bookingSuccessRate' => 95,
            'cancellationRate' => 2.3,
        ]);
    }
}
