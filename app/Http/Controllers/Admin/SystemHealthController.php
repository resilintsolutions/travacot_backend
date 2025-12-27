<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailabilityHealthDaily;
use App\Models\BookingHealthDaily;
use App\Models\PricingHealthDaily;
use App\Models\PricingHealthIssueDaily;
use App\Models\HealthEventLog;

class SystemHealthController extends Controller
{
    public function index()
    {
        $date = now()->subDay()->toDateString();

        /* ==========================================================
         | TOP HEALTH CARDS (GLOBAL)
         ========================================================== */

        $availability = AvailabilityHealthDaily::where('date', $date)
            ->selectRaw('
                COALESCE(SUM(total_requests),0) as total_requests,
                COALESCE(SUM(success_count),0) as success_count,
                COALESCE(SUM(failure_count),0) as failure_count
            ')
            ->first();

        $booking = BookingHealthDaily::where('date', $date)
            ->selectRaw('
                COALESCE(SUM(total_attempts),0) as total_attempts,
                COALESCE(SUM(success_count),0) as success_count,
                COALESCE(SUM(failure_count),0) as failure_count,
                COALESCE(SUM(refund_count),0) as refund_count
            ')
            ->first();

        $pricing = PricingHealthDaily::where('date', $date)
            ->selectRaw('
                COALESCE(SUM(total_quotes),0) as total_quotes,
                COALESCE(SUM(below_msp_count),0) as below_msp_count,
                COALESCE(SUM(missing_tax_count),0) as missing_tax_count
            ')
            ->first();

        /* ==========================================================
         | AVAILABILITY BY COUNTRY
         ========================================================== */

        $availabilityByCountry = AvailabilityHealthDaily::where('date', $date)
            ->where('country', '!=', 'ALL')
            ->orderBy('country')
            ->get()
            ->map(fn ($row) => [
                'country'       => $row->country,
                'success_rate'  => $row->total_requests > 0
                    ? round(($row->success_count / $row->total_requests) * 100)
                    : 0,
                'avg_response'  => $row->avg_response_time_ms
                    ? round($row->avg_response_time_ms / 1000, 2) . 's'
                    : '—',
                'timeouts'      => $row->timeout_count ?? 0,
                'no_rooms_rate' => $row->hotels_returned_avg ?? 0,
            ]);

        /* ==========================================================
         | MAPPING & CONTENT HEALTH
         ========================================================== */

        $contentIssues = HealthEventLog::whereBetween('created_at', [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ])
            ->whereIn('domain', ['content', 'mapping'])
            ->get()
            ->groupBy(fn ($e) => data_get($e->meta, 'reason', 'unknown'))
            ->map(fn ($rows, $reason) => [
                'label'   => ucfirst(str_replace('_', ' ', $reason)),
                'count'   => $rows->count(),
                'finding' => match ($reason) {
                    'missing_images'       => 'High availability, stable prices',
                    'wrong_coordinates'    => 'Slow responses, missing prices',
                    'wrong_destination'    => 'Low availability, high timeout rate',
                    'missing_descriptions' => 'Recheck failures',
                    'no_room_types'        => 'Recheck failures',
                    default                => 'Review needed',
                },
            ])
            ->values();

        /* ==========================================================
         | RECHECK HEALTH
         ========================================================== */

        $recheckEvents = HealthEventLog::whereBetween('created_at', [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ])
            ->where('domain', 'recheck')
            ->get();

        $recheckTotal = $recheckEvents->count();

        $recheckSuccessRate = $recheckTotal > 0
            ? round(($recheckEvents->where('status', 'success')->count() / $recheckTotal) * 100)
            : 0;

        $recheckStats = [
            'success_rate' => $recheckSuccessRate,
            'rows' => [
                [
                    'label'  => 'Pre-Booking Recheck Success Rate',
                    'value'  => $recheckSuccessRate . '%',
                    'danger' => $recheckSuccessRate < 80,
                ],
                [
                    'label'  => 'Price Increase Rate',
                    'value'  => $recheckTotal > 0
                        ? round(
                            ($recheckEvents->filter(
                                fn ($e) => data_get($e->meta, 'price_changed') === true
                            )->count() / $recheckTotal) * 100
                        ) . '%'
                        : '0%',
                    'danger' => true,
                ],
                [
                    'label'  => 'Room Not Available',
                    'value'  => $recheckTotal > 0
                        ? round(
                            ($recheckEvents->filter(
                                fn ($e) => data_get($e->meta, 'no_rooms') === true
                            )->count() / $recheckTotal) * 100
                        ) . '%'
                        : '0%',
                    'danger' => true,
                ],
                [
                    'label'  => 'Supplier Error Codes',
                    'value'  => '301, 401 Daily',
                    'danger' => true,
                ],
            ],
        ];

        /* ==========================================================
         | BOOKING ENGINE HEALTH
         ========================================================== */

        $bookingStats = [
            [
                'label'  => 'Post-Booking Success Rate',
                'value'  => $booking->total_attempts > 0
                    ? round(($booking->success_count / $booking->total_attempts) * 100) . '%'
                    : '0%',
                'danger' => false,
            ],
            [
                'label'  => 'Booking Failures (24h)',
                'value'  => $booking->failure_count,
                'danger' => true,
            ],
            [
                'label'  => 'Refund Queue',
                'value'  => $booking->refund_count . ' Pending',
                'danger' => true,
            ],
        ];

        /* ==========================================================
         | PRICING / QUOTE HEALTH
         ========================================================== */

        $pricingIssues = PricingHealthIssueDaily::where('date', $date)
            ->get()
            ->keyBy('issue');

        $getIssue = fn ($key) => $pricingIssues[$key]->total ?? 0;

        $totalQuotes = max(1, $pricing->total_quotes);

        $pricingStats = [
            [
                'label'    => 'Quote Success Rate',
                'value'    => round((($totalQuotes - $pricing->below_msp_count) / $totalQuotes) * 100) . '%',
                'finding'  => 'Warning',
                'severity' => 'text-yellow-600',
            ],
            [
                'label'    => 'Price Changed on Recheck',
                'value'    => round(($getIssue('price_changed') / $totalQuotes) * 100) . '%',
                'finding'  => 'Medium',
                'severity' => 'text-yellow-600',
            ],
            [
                'label'    => 'Missing Taxes',
                'value'    => $getIssue('missing_tax') . ' Hotels',
                'finding'  => 'Medium',
                'severity' => 'text-yellow-600',
            ],
            [
                'label'    => 'Below MSP Rates',
                'value'    => $getIssue('below_msp') . ' Hotels',
                'finding'  => 'Critical',
                'severity' => 'text-red-600',
            ],
            [
                'label'    => 'Missing Price (Zero)',
                'value'    => $getIssue('zero_rate') . ' Hotels',
                'finding'  => 'Critical',
                'severity' => 'text-red-600',
            ],
        ];

        return view('admin.system-health.api-health', compact(
            'availability',
            'booking',
            'pricing',
            'availabilityByCountry',
            'contentIssues',
            'recheckStats',
            'bookingStats',
            'pricingStats'
        ));
    }
}
