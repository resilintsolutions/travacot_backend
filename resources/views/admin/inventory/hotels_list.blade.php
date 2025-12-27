<x-app-layout>

@section('title', 'Inventory - Hotels List')

@section('content')

<div class="inv-page-wrapper">
    <div class="mb-3 text-xs text-gray-500">
        Inventory <span class="mx-1">›</span> Hotels List
    </div>

    <div class="inv-card">
        {{-- Search bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="inv-header-title">Search for hotels</div>
                <div class="inv-page-title">Hotels List</div>
            </div>
            <form method="GET" action="{{ route('admin.inventory.hotels_list') }}" class="d-flex align-items-center" style="gap:12px;">
                <div style="position:relative; min-width: 280px;">
                    <span class="inv-search-icon">
                        🔍
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ old('search', $search) }}"
                        class="form-control inv-search-input"
                        placeholder="Search Hotels"
                    >
                </div>

                {{-- Optional supplier filter --}}
                <select name="supplier" class="form-select form-select-sm" style="width: 140px; border-radius:999px;">
                    <option value="">All suppliers</option>
                    <option value="Hotelbeds" {{ $supplier === 'Hotelbeds' ? 'selected' : '' }}>Hotelbeds</option>
                    {{-- add more suppliers if needed --}}
                </select>

                <button type="submit" class="btn btn-primary btn-sm" style="border-radius:999px; padding-inline:18px;">
                    Search
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="inv-table-wrapper">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">Hotel</th>
                        <th style="width: 12%;">Supplier</th>
                        <th style="width: 20%;">API Health for the hotel<br><span style="font-weight:400;">(Success/Failed/Sold Out/Price Mismatch/Error Codes)</span></th>
                        <th style="width: 12%;">Inventory<br><span style="font-weight:400;">Rooms Available Today</span></th>
                        <th style="width: 12%;">Price Range</th>
                        <th style="width: 16%;">Status<br><span style="font-weight:400;">(Active/Inactive/Suspended)</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hotels as $hotel)
                        @php
                            // Example derived values — adjust for your schema
                            $supplier   = $hotel->vendor ?? 'Hotelbeds';
                            $roomsToday = $hotel->rooms_available_today ?? 12; // placeholder
                            $priceMin   = $hotel->lowest_rate;
                            $priceMax   = $hotel->highest_rate ?? null;
                            $currency   = $hotel->currency ?? 'USD';

                            // crude API health example (customise with real logic/logs)
                            $apiHealthLabel = 'Sold Out';
                            $apiHealthClass = 'pill-warning';

                            // status pill
                            $status = strtolower($hotel->status ?? 'active');
                            if ($status === 'active') {
                                $statusClass = 'status-pill status-pill--active';
                                $statusLabel = 'Active Property';
                            } elseif ($status === 'suspended') {
                                $statusClass = 'status-pill status-pill--suspended';
                                $statusLabel = 'Suspended Property';
                            } else {
                                $statusClass = 'status-pill status-pill--inactive';
                                $statusLabel = 'Inactive Property';
                            }
                        @endphp
                        <tr>
                            {{-- Hotel name + location --}}
                            <td>
                                <a href="{{route('admin.hotels.show',$hotel->id)}}">
                                <div class="inv-hotel-name">
                                    {{ $hotel->name ?? 'Unnamed hotel' }}
                                </div>
                                <div class="inv-hotel-location">
                                    {{ trim(($hotel->city ?? '') . ', ' . ($hotel->country ?? '')) ?: '—' }}
                                </div>
                                </a>
                            </td>

                            {{-- Supplier --}}
                            <td>
                                <span class="pill pill-muted">
                                    {{ $supplier }}
                                </span>
                            </td>

                            {{-- API Health --}}
                            <td>
                                <span class="pill {{ $apiHealthClass }}">
                                    {{ $apiHealthLabel }}
                                </span>
                            </td>

                            {{-- Inventory / rooms today --}}
                            <td>
                                {{ $roomsToday ?? '—' }}
                            </td>

                            {{-- Price Range --}}
                            <td>
                                @if($priceMin)
                                    @if($priceMax)
                                        {{ $currency }} {{ number_format($priceMin, 0) }} – {{ number_format($priceMax, 0) }}
                                    @else
                                        {{ $currency }} {{ number_format($priceMin, 0) }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <div class="{{ $statusClass }}">
                                    <span class="status-dot"></span>
                                    <span>{{ $statusLabel }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hotels found. Try adjusting your search filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size: 12px;">
                Showing
                @if($hotels->count())
                    {{ $hotels->firstItem() }}–{{ $hotels->lastItem() }}
                @else
                    0
                @endif
                of {{ $hotels->total() }} hotels
            </div>
            <div>
                {{ $hotels->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
</x-app-layout>
