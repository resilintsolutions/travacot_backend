<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card mb-3 p-3">
            <form class="d-flex gap-2" method="GET" action="{{ route('admin.ppm.index') }}">
                <select name="country" class="form-select form-select-sm" style="width: 180px;">
                    <option value="">All countries</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected($filters['country'] == $country)>{{ $country }}</option>
                    @endforeach
                </select>

                <div class="input-group input-group-sm" style="width: 260px;">
                    <input type="text" class="form-control" name="q"
                        placeholder="Search hotels, city or country..." value="{{ $filters['q'] }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>

                <a href="{{ route('admin.ppm.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="mb-3">PPM (Price and Performance Management)</h3>
                <p class="text-muted small">The system resolves MSP and margin rule using <strong>City → Country →
                        Global</strong> precedence.</p>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hotel</th>
                                <th class="text-center">Vendor Net</th>
                                <th class="text-center">Effective Min (MSP)</th>
                                <th class="text-center">Final Margin</th>
                                <th class="text-center">Rate in Market</th>
                                <th class="text-center">Bookings 24h</th>
                                <th class="text-center">Revenue (this month)</th>
                                <th class="text-center">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotels as $hotel)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $hotel->name }}</div>
                                        <div class="text-muted small">
                                            {{ $hotel->destination_name ?? $hotel->city }}, {{ $hotel->country }}
                                        </div>

                                        <div class="mt-2">
                                            {{-- MSP scope --}}
                                            <span class="badge bg-secondary">
                                                MSP: {{ $hotel->msp_scope ?? 'None' }}
                                            </span>

                                            {{-- Margin Rule Scope --}}
                                            <span class="badge bg-primary ms-1">
                                                Rule: {{ $hotel->rules_scope ?? 'None' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Vendor Net --}}
                                    <td class="text-center">
                                        @if (!is_null($hotel->vendor_net) && $hotel->vendor_net > 0)
                                            US$ {{ number_format($hotel->vendor_net, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Effective Min Price --}}
                                    <td class="text-center">
                                        US$ {{ number_format($hotel->effective_min_price ?? 0, 2) }}
                                    </td>

                                    {{-- Final Margin --}}
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                            <span
                                                class="fw-semibold">{{ number_format($hotel->final_margin_percent ?? 0, 2) }}</span>
                                            <span class="ms-1">%</span>
                                        </div>
                                    </td>

                                    {{-- Rate in Market --}}
                                    <td class="text-center">
                                        <div class="border rounded-pill px-3 py-1">
                                            @if ($hotel->rate_in_market)
                                                US$ {{ number_format($hotel->rate_in_market, 2) }}
                                            @else
                                                —
                                            @endif
                                        </div>

                                        @php
                                            $min = $hotel->effective_min_price ?? 0;
                                            $rate = $hotel->rate_in_market ?? 0;
                                        @endphp

                                        <small
                                            class="
                @if ($rate > $min) text-success
                @elseif($rate < $min) text-danger
                @else text-muted @endif">
                                            @if ($rate > $min)
                                                Above minimum price
                                            @elseif($rate < $min)
                                                Below minimum price
                                            @else
                                                At minimum price
                                            @endif
                                        </small>
                                    </td>

                                    {{-- Bookings last 24 hrs --}}
                                    <td class="text-center fw-semibold">
                                        {{ $hotel->bookings_24h ?? 0 }}
                                    </td>

                                    {{-- Revenue --}}
                                    <td class="text-center">
                                        <div class="fw-semibold">
                                            US$ {{ number_format($hotel->revenue_this_month ?? 0, 2) }}
                                        </div>

                                        <div class="small {{ $hotel->revenue_trend_class }}">
                                            @if ($hotel->revenue_last_month == 0)
                                                — No data last month
                                            @else
                                                @php $diffAbs = abs($hotel->revenue_diff); @endphp

                                                @if ($hotel->revenue_diff < 0)
                                                    − US$ {{ number_format($diffAbs, 2) }}
                                                @else
                                                    ▲ US$ {{ number_format($diffAbs, 2) }}
                                                @endif

                                                {{ $hotel->revenue_trend_label }}
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Final Decision --}}
                                    <td class="text-center">
                                        <span class="{{ $hotel->final_decision_class }}">
                                            {{ $hotel->final_decision_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hotels found with current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
