<x-app-layout>
    <x-slot name="title">Today&apos;s Performance</x-slot>

    {{-- reuse same styles from inventory pages --}}
    <style>
        .inv-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 28px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.05);
        }
        .inv-header-title {
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .inv-page-title {
            font-size: 22px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }
        .inv-subtitle {
            font-size: 12px;
            color: #9ca3af;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }
        .pill-success { background:#ecfdf5; color:#166534; border:1px solid #bbf7d0; }
        .pill-warning { background:#fffbeb; color:#92400e; border:1px solid #fed7aa; }
        .pill-danger  { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .pill-muted   { background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb; }

        .inv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .inv-table th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            white-space: nowrap;
        }
        .inv-table td {
            font-size: 13px;
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .metric-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .metric-value {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        .metric-change {
            font-size: 11px;
        }
        .metric-change i {
            font-size: 13px;
        }
    </style>

    <div class="container-fluid py-4">
        {{-- Breadcrumb & title --}}
        <div class="mb-3">
            <div class="inv-subtitle">Home &gt; Today&apos;s Performance</div>
            <div class="inv-page-title mt-1">Today&apos;s Performance</div>
        </div>

        <div class="row g-4">
            {{-- UPCOMING STAYS --}}
            <div class="col-12">
                <div class="inv-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="inv-header-title">Upcoming Stays / Check-ins Today</div>
                            <div class="inv-subtitle">
                                {{ count($upcomingStays) }} upcoming stays
                            </div>
                        </div>
                        {{-- optional "View more" button --}}
                        {{-- <a href="#" class="btn btn-sm btn-outline-secondary">View more →</a> --}}
                    </div>

                    <div class="table-responsive">
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Hotel Booked</th>
                                    <th>Destination</th>
                                    <th>Confirmation Number</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Booking Channel</th>
                                    <th>Customer Paid</th>
                                    <th>Status</th>
                                    <th>API Call</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingStays as $stay)
                                    <tr>
                                        <td>{{ $stay['guest_name'] }}</td>
                                        <td>{{ $stay['hotel_name'] }}</td>
                                        <td>{{ $stay['destination'] ?: '—' }}</td>
                                        <td>{{ $stay['confirmation'] ?: '—' }}</td>
                                        <td>{{ $stay['check_in'] }}</td>
                                        <td>{{ $stay['check_out'] }}</td>
                                        <td>{{ $stay['channel'] }}</td>
                                        <td>
                                            {{ number_format($stay['customer_paid'], 2) }}
                                            <span class="inv-subtitle">{{ $stay['currency'] }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $s = $stay['status'];
                                                $cls = 'pill-muted';
                                                if ($s === 'confirmed') $cls = 'pill-success';
                                                elseif ($s === 'cancelled') $cls = 'pill-danger';
                                                elseif ($s === 'pending') $cls = 'pill-warning';
                                            @endphp
                                            <span class="pill {{ $cls }}">
                                                {{ ucfirst($s) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $api = $stay['api_call'];
                                                $apiCls = $api === 'Success' ? 'pill-success' : ($api === 'Failed' ? 'pill-danger' : 'pill-muted');
                                            @endphp
                                            <span class="pill {{ $apiCls }}">{{ $api }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-3">
                                            No reservations created today.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- LEFT COLUMN CARDS --}}
            <div class="col-lg-6 d-flex flex-column gap-4">
                {{-- Search Activity --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">Today&apos;s Search Activity</div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="metric-label mb-1">Searches with no results</div>
                            <div class="metric-value">{{ $searchActivity['no_results'] ?? 0 }}</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">High response times</div>
                            <div class="metric-value">{{ $searchActivity['high_response'] ?? 0 }}</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">Total searches today</div>
                            <div class="metric-value">{{ $searchActivity['total_searches'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- Issues Today (very simple for now – based on statuses / errors) --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">Issues Today</div>
                    <div class="inv-subtitle mb-3">
                        Based on reservations and API error logs today
                    </div>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="metric-label mb-1">Overbookings</div>
                            <div class="metric-value">0</div>
                        </div>
                        <div class="col-3">
                            <div class="metric-label mb-1">Price mismatches</div>
                            <div class="metric-value">0</div>
                        </div>
                        <div class="col-3">
                            <div class="metric-label mb-1">Parity issues</div>
                            <div class="metric-value">0</div>
                        </div>
                        <div class="col-3">
                            <div class="metric-label mb-1">API errors</div>
                            <div class="metric-value">{{ $apiErrorsToday['error_count'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- Booking Summary --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">Today&apos;s Booking Summary</div>
                    <div class="row">
                        @php
                            $bs = $bookingSummary;
                        @endphp
                        <div class="col-4">
                            <div class="metric-label mb-1">Successful</div>
                            <div class="metric-value">{{ $bs['success_today'] }}</div>
                            <div class="metric-change mt-1">
                                @if($bs['success_diff'] > 0)
                                    <span class="text-success">
                                        <i class="mdi mdi-arrow-up-bold"></i>
                                        Higher than yesterday (+{{ $bs['success_diff'] }})
                                    </span>
                                @elseif($bs['success_diff'] < 0)
                                    <span class="text-danger">
                                        <i class="mdi mdi-arrow-down-bold"></i>
                                        Lower than yesterday ({{ $bs['success_diff'] }})
                                    </span>
                                @else
                                    <span class="text-muted">Same as yesterday</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="metric-label mb-1">Failed</div>
                            <div class="metric-value">{{ $bs['failed_today'] }}</div>
                            <div class="metric-change mt-1">
                                @if($bs['failed_diff'] > 0)
                                    <span class="text-danger">
                                        <i class="mdi mdi-arrow-up-bold"></i>
                                        Higher than yesterday (+{{ $bs['failed_diff'] }})
                                    </span>
                                @elseif($bs['failed_diff'] < 0)
                                    <span class="text-success">
                                        <i class="mdi mdi-arrow-down-bold"></i>
                                        Lower than yesterday ({{ $bs['failed_diff'] }})
                                    </span>
                                @else
                                    <span class="text-muted">Same as yesterday</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="metric-label mb-1">Cancelled</div>
                            <div class="metric-value">{{ $bs['cancelled_today'] }}</div>
                            <div class="metric-change mt-1">
                                @if($bs['cancelled_diff'] > 0)
                                    <span class="text-danger">
                                        <i class="mdi mdi-arrow-up-bold"></i>
                                        Higher than yesterday (+{{ $bs['cancelled_diff'] }})
                                    </span>
                                @elseif($bs['cancelled_diff'] < 0)
                                    <span class="text-success">
                                        <i class="mdi mdi-arrow-down-bold"></i>
                                        Lower than yesterday ({{ $bs['cancelled_diff'] }})
                                    </span>
                                @else
                                    <span class="text-muted">Same as yesterday</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN CARDS --}}
            <div class="col-lg-6 d-flex flex-column gap-4">
                {{-- Top Performing Supplier --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">Today&apos;s Top Performing Supplier</div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="metric-label mb-1">Most Bookings</div>
                            <div class="metric-value">
                                {{ $topSupplier['most_bookings'] ?? '—' }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">Highest Conversion</div>
                            <div class="metric-value">
                                {{ $topSupplier['highest_conversion'] ?? '—' }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">Status</div>
                            @php
                                $status = $topSupplier['status'] ?? 'No data';
                                $cls = $status === 'High' ? 'pill-success'
                                      : ($status === 'Medium' ? 'pill-warning'
                                      : ($status === 'Low' ? 'pill-danger' : 'pill-muted'));
                            @endphp
                            <div class="mt-1">
                                <span class="pill {{ $cls }}">{{ $status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- API Errors Today --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">API Errors TODAY</div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="metric-label mb-1">Supplier</div>
                            <div class="metric-value">
                                {{ $apiErrorsToday['supplier'] ?? '—' }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">Number of API Errors</div>
                            <div class="metric-value">
                                {{ $apiErrorsToday['error_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-label mb-1">Severity</div>
                            @php
                                $sev = $apiErrorsToday['severity'] ?? 'None';
                                $sevCls = $sev === 'High' ? 'pill-danger'
                                         : ($sev === 'Medium' ? 'pill-warning'
                                         : 'pill-success');
                            @endphp
                            <div class="mt-1">
                                <span class="pill {{ $sevCls }}">{{ $sev }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revenue Today --}}
                <div class="inv-card h-100">
                    <div class="inv-header-title mb-2">Today&apos;s Revenue</div>
                    @php
                        $rev = $revenue;
                    @endphp
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-label mb-1">Revenue Today</div>
                            <div class="metric-value">
                                {{ number_format($rev['total_today'], 2) }}
                            </div>
                            <div class="metric-change mt-1">
                                @if($rev['total_diff'] > 0)
                                    <span class="text-success">
                                        <i class="mdi mdi-arrow-up-bold"></i>
                                        Higher than yesterday (+{{ number_format($rev['total_diff'], 2) }})
                                    </span>
                                @elseif($rev['total_diff'] < 0)
                                    <span class="text-danger">
                                        <i class="mdi mdi-arrow-down-bold"></i>
                                        Lower than yesterday ({{ number_format($rev['total_diff'], 2) }})
                                    </span>
                                @else
                                    <span class="text-muted">Same as yesterday</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-label mb-1">Avg. Booking Value</div>
                            <div class="metric-value">
                                {{ number_format($rev['avg_today'], 2) }}
                            </div>
                            <div class="metric-change mt-1">
                                @if($rev['avg_diff'] > 0)
                                    <span class="text-success">
                                        <i class="mdi mdi-arrow-up-bold"></i>
                                        Higher than yesterday (+{{ number_format($rev['avg_diff'], 2) }})
                                    </span>
                                @elseif($rev['avg_diff'] < 0)
                                    <span class="text-danger">
                                        <i class="mdi mdi-arrow-down-bold"></i>
                                        Lower than yesterday ({{ number_format($rev['avg_diff'], 2) }})
                                    </span>
                                @else
                                    <span class="text-muted">Same as yesterday</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- row --}}
    </div> {{-- container --}}
</x-app-layout>
