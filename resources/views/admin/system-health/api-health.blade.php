<x-app-layout>

    {{-- Breadcrumb --}}
        <div class="mb-3 text-xs text-gray-500">
        System Health &gt;
        <span class="font-medium text-gray-900">
            API Health
        </span>
    </div>
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                
                <h3 class="text-3xl font-semibold mb-1">API Health Monitoring</h3>
                <p class="text-sm text-gray-500 mb-8">
                    Daily aggregated performance & reliability metrics
                </p>


                {{-- ================= TOP CARDS ================= --}}
                @php
                    $availabilityPct = $availability?->total_requests
                        ? round(($availability->success_count / $availability->total_requests) * 100)
                        : 0;

                    $bookingPct = $booking?->total_attempts
                        ? round(($booking->success_count / $booking->total_attempts) * 100)
                        : 0;

                    $pricingPct = $pricing?->total_quotes
                        ? round((($pricing->total_quotes - $pricing->below_msp_count) / $pricing->total_quotes) * 100)
                        : 0;
                @endphp

                <div class="row mb-4">

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="p-4 rounded border bg-success-subtle border-success">
                            <div class="text-muted">Availability Health</div>
                            <div class="fs-3 fw-semibold text-success">
                                {{ $availabilityPct }}%
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="p-4 rounded border bg-warning-subtle border-warning">
                            <div class="text-muted">Pricing API Health</div>
                            <div class="fs-3 fw-semibold text-warning">
                                {{ $pricingPct }}%
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="p-4 rounded border bg-danger-subtle border-danger">
                            <div class="text-muted">Recheck API Health</div>
                            <div class="fs-3 fw-semibold text-danger">
                                {{ $recheckStats['success_rate'] ?? 0 }}%
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="p-4 rounded border bg-success-subtle border-success">
                            <div class="text-muted">Booking Engine Health</div>
                            <div class="fs-3 fw-semibold text-success">
                                {{ $bookingPct }}%
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ================= OPERATIONAL HEALTH ================= --}}
                <h3 class="font-semibold mb-4">Operational Health</h3>

                {{-- Mapping & Content --}}
                <div class="bg-white border rounded-xl mb-8">
                    <div class="p-6 border-b">
                        <h3 class="font-medium">Mapping & Content Health</h3>
                        <p class="text-sm text-gray-500">Data quality indicators</p>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="p-4 text-left">Issue</th>
                                <th class="text-center">Count</th>
                                <th class="p-4 text-right">Findings</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($contentIssues as $issue)
                                <tr>
                                    <td class="p-4">{{ $issue['label'] }}</td>
                                    <td class="text-center">{{ $issue['count'] }}</td>
                                    <td class="p-4 text-right text-gray-500">
                                        {{ $issue['finding'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Recheck + Booking --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                    {{-- Recheck --}}
                    <div class="bg-white border rounded-xl p-6">
                        <h3 class="font-medium mb-4">Recheck (Before Booking) Health</h3>

                        @foreach ($recheckStats['rows'] ?? [] as $row)
                            <div class="flex justify-between text-sm py-2 border-b last:border-0">
                                <span>{{ $row['label'] }}</span>
                                <span class="font-medium {{ $row['danger'] ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ $row['value'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Booking --}}
                    <div class="bg-white border rounded-xl p-6">
                        <h3 class="font-medium mb-4">Booking Engine Health</h3>

                        @foreach ($bookingStats as $row)
                            <div class="flex justify-between text-sm py-2 border-b last:border-0">
                                <span>{{ $row['label'] }}</span>
                                <span class="{{ $row['danger'] ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $row['value'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                    {{-- Availability by Country --}}
                    <div class="bg-white border rounded-xl p-6">
                        <h3 class="font-medium mb-4">Availability Health</h3>

                        <table class="w-full text-sm">
                            <thead class="border-b text-gray-500">
                                <tr>
                                    <th class="text-left py-2">Country</th>
                                    <th class="text-center">Success</th>
                                    <th class="text-center">Avg. Response</th>
                                    <th class="text-center">Timeouts</th>
                                    <th class="text-center">No Rooms</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($availabilityByCountry as $row)
                                    <tr>
                                        <td class="py-3 font-medium">{{ $row->country }}</td>
                                        <td class="text-center text-green-600">
                                            {{ $row->success_rate }}%
                                        </td>
                                        <td class="text-center">
                                            {{ $row->avg_response_time_ms }} ms
                                        </td>
                                        <td class="text-center">
                                            {{ $row->timeout_pct }}%
                                        </td>
                                        <td class="text-center">
                                            {{ $row->no_rooms_pct }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pricing / Quote --}}
                    <div class="bg-white border rounded-xl p-6">
                        <h3 class="font-medium mb-4">Pricing / Quote Health</h3>

                        <table class="w-full text-sm">
                            <tbody class="divide-y">
                                @foreach ($pricingStats as $row)
                                    <tr>
                                        <td class="py-3">{{ $row['label'] }}</td>
                                        <td class="text-right font-medium">
                                            {{ $row['value'] }}
                                        </td>
                                        <td class="text-right {{ $row['severity'] }}">
                                            {{ $row['finding'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
