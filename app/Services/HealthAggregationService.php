<?php

namespace App\Services;

use App\Models\HealthEventLog;
use App\Models\AvailabilityHealthDaily;
use App\Models\BookingHealthDaily;
use App\Models\PricingHealthDaily;
use App\Models\PricingHealthIssueDaily;
use Illuminate\Support\Carbon;

class HealthAggregationService
{
    /* ======================================================
     * AVAILABILITY
     * ====================================================== */
    public function aggregateAvailabilityDaily(Carbon $date): void
    {
        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $events = HealthEventLog::where('domain', 'availability')
            ->whereBetween('event_date', [$from, $to])
            ->get();

        if ($events->isEmpty()) {
            return;
        }

        $grouped = $events->groupBy(function ($e) {
            return data_get($e->meta, 'supplier', 'hotelbeds')
                . '|'
                . data_get($e->meta, 'country', 'ALL');
        });

        foreach ($grouped as $key => $rows) {
            [$supplier, $country] = explode('|', $key);

            $times = $rows->pluck('response_time_ms')
                ->filter(fn ($v) => is_numeric($v))
                ->sort()
                ->values();

            $count = $times->count();
            $p95   = $count > 0
                ? $times[(int) floor(($count - 1) * 0.95)]
                : null;

            AvailabilityHealthDaily::updateOrCreate(
                [
                    'date'     => $date->toDateString(),
                    'supplier' => $supplier,
                    'country'  => $country,
                ],
                [
                    'total_requests'       => $rows->count(),
                    'success_count'        => $rows->where('status', 'success')->count(),
                    'failure_count'        => $rows->where('status', 'failure')->count(),
                    'timeout_count'        => $rows->filter(
                        fn ($e) => data_get($e->meta, 'timeout') === true
                    )->count(),
                    'avg_response_time_ms' => $times->isNotEmpty()
                        ? round($times->avg())
                        : null,
                    'p95_response_time_ms' => $p95,
                    'hotels_returned_avg'  => round(
                        $rows->pluck('meta.hotel_count')
                            ->filter(fn ($v) => is_numeric($v))
                            ->avg() ?? 0
                    ),
                ]
            );
        }
    }

    /* ======================================================
     * BOOKING
     * ====================================================== */
    public function aggregateBookingDaily(Carbon $date): void
    {
        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $events = HealthEventLog::where('domain', 'booking')
            ->whereBetween('event_date', [$from, $to])
            ->get();

        if ($events->isEmpty()) {
            return;
        }

        $grouped = $events->groupBy(
            fn ($e) => data_get($e->meta, 'supplier', 'hotelbeds')
        );

        foreach ($grouped as $supplier => $rows) {
            BookingHealthDaily::updateOrCreate(
                [
                    'date'     => $date->toDateString(),
                    'supplier' => $supplier,
                ],
                [
                    'total_attempts'       => $rows->count(),
                    'success_count'        => $rows->where('status', 'success')->count(),
                    'failure_count'        => $rows->where('status', 'failure')->count(),
                    'refund_count'         => $rows->filter(
                        fn ($e) => data_get($e->meta, 'refunded') === true
                    )->count(),
                    'avg_response_time_ms' => round(
                        $rows->avg('response_time_ms') ?? 0
                    ),
                ]
            );
        }
    }

    /* ======================================================
     * PRICING / QUOTE
     * ====================================================== */
    public function aggregatePricingDaily(Carbon $date): void
    {
        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $events = HealthEventLog::where('domain', 'pricing')
            ->whereBetween('event_date', [$from, $to])
            ->get();

        if ($events->isEmpty()) {
            return;
        }

        $groupedBySupplier = $events->groupBy(
            fn ($e) => data_get($e->meta, 'supplier', 'hotelbeds')
        );

        /* ---------- RESET DAILY ISSUES (IMPORTANT) ---------- */
        PricingHealthIssueDaily::whereDate('date', $date)->delete();

        foreach ($groupedBySupplier as $supplier => $rows) {

            PricingHealthDaily::updateOrCreate(
                [
                    'date'     => $date->toDateString(),
                    'supplier' => $supplier,
                ],
                [
                    'total_quotes'      => $rows->count(),
                    'below_msp_count'   => $rows->filter(
                        fn ($e) => data_get($e->meta, 'below_msp') === true
                    )->count(),
                    'missing_tax_count' => $rows->filter(
                        fn ($e) => data_get($e->meta, 'missing_tax') === true
                    )->count(),
                ]
            );

            $issues = [
                'below_msp',
                'missing_tax',
                'zero_rate',
                'price_changed',
            ];

            foreach ($issues as $issue) {
                $count = $rows->filter(
                    fn ($e) => data_get($e->meta, $issue) === true
                )->count();

                if ($count === 0) {
                    continue;
                }

                PricingHealthIssueDaily::create([
                    'date'     => $date->toDateString(),
                    'supplier' => $supplier,
                    'issue'    => $issue,
                    'total'    => $count,
                ]);
            }
        }
    }
}
