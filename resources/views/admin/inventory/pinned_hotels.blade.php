<x-app-layout>
    @php
        $search = $search ?? '';
    @endphp

    <div class="inv-page-wrapper">
        {{-- Breadcrumb --}}
        <div class="mb-2 text-muted" style="font-size: 12px;">
            Inventory <span class="mx-1">›</span> Pinned Hotels
        </div>

        <div class="inv-card">
            {{-- Top row: search + Pin/Unpin pills --}}
            <div class="inv-top-row mb-3">
                {{-- Left: search block --}}
                <div class="inv-search-block">
                    <div class="inv-header-title">Search for hotels</div>
                    <div class="inv-page-title">Pinned Hotels</div>

                    <form method="GET"
                          action="{{ route('admin.inventory.pinned.index') }}"
                          class="inv-search-row">
                        <div style="position:relative; flex: 1;">
                            <span class="inv-search-icon">
                                🔍
                            </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                class="form-control inv-search-input"
                                placeholder="Search Hotels"
                            >
                        </div>

                        <button type="submit" class="btn btn-dark rounded-pill px-4">
                            Search
                        </button>
                    </form>
                </div>

                {{-- Right: Pin / Unpin explanation pills --}}
                <div class="inv-pin-pills">
                    <div class="inv-pin-pill ">
                        <div class="inv-pin-pill-title inv-pin-pill--pin">Pin</div>
                        <p>Boosts the hotel in search results:</p>

                        <ul>
                            <li>Featured in deals / offers</li>
                            <li>Shown in home page carousels</li>
                            <li>Appears in “recommended” slots</li>
                        </ul>
                    </div>

                    <div class="inv-pin-pill ">
                        <div class="inv-pin-pill-title inv-pin-pill--unpin">Unpin</div>
                        <p>Returns hotel to normal ranking:</p>
                        <ul>
                            <li>Still visible everywhere</li>
                            <li>Still eligible for deals/carousels/recommendations</li>
                            <li>No manual boost</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="inv-table-wrapper">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th style="width: 26%;">Hotel</th>
                            <th style="width: 10%;">Supplier</th>
                            <th style="width: 16%;">API Health for the hotel
                                <br><span style="font-weight:400;">(Success/Failed/Sold Out/Price Mismatch/Error Codes)</span>
                            </th>
                            <th style="width: 12%;">Inventory<br><span style="font-weight:400;">Rooms Available Today</span></th>
                            <th style="width: 12%;">Price Mismatches</th>
                            <th style="width: 10%;">Bookings (7d)</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 6%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pinnedHotels as $row)
                            @php
                                $hotel         = $row->hotel;
                                $apiSuccess    = $row->api_success_rate ?? null;
                                $roomsToday    = $row->inventory_rooms_today ?? ($hotel->rooms_available_today ?? null);
                                $mismatches    = $row->price_mismatches ?? 0;
                                $bookings7d    = $row->bookings_7d ?? 0;

                                if (is_null($apiSuccess)) {
                                    $apiColor = '#9ca3af';
                                } elseif ($apiSuccess < 70) {
                                    $apiColor = '#b91c1c';
                                } elseif ($apiSuccess < 90) {
                                    $apiColor = '#f59e0b';
                                } else {
                                    $apiColor = '#166534';
                                }

                                $statusRaw = strtolower($hotel->status ?? 'active');
                                if ($statusRaw === 'active') {
                                    $statusClass = 'status-pill status-pill--active';
                                    $statusLabel = 'Active';
                                } elseif ($statusRaw === 'suspended' || $statusRaw === 'warning') {
                                    $statusClass = 'status-pill status-pill--suspended';
                                    $statusLabel = 'Warning';
                                } else {
                                    $statusClass = 'status-pill status-pill--inactive';
                                    $statusLabel = 'Inactive';
                                }

                                $isPinned = $row->is_pinned ?? true;
                            @endphp
                            <tr>
                                {{-- Hotel --}}
                                <td>
                                    <div class="inv-hotel-name">
                                        {{ $hotel->name ?? 'Unnamed hotel' }}
                                    </div>
                                    <div class="inv-hotel-location">
                                        {{ trim(($hotel->city ?? '') . ', ' . ($hotel->country ?? '')) ?: '—' }}
                                    </div>
                                </td>

                                {{-- Supplier --}}
                                <td>
                                    <span class="pill pill-muted">
                                        {{ $hotel->vendor ?? 'Hotelbeds' }}
                                    </span>
                                </td>

                                {{-- API Health --}}
                                <td>
                                    <div class="d-flex align-items-center" style="gap:8px;">
                                        <span class="status-dot" style="background: {{ $apiColor }};"></span>
                                        <span>
                                            @if(!is_null($apiSuccess))
                                                {{ $apiSuccess }}%
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                {{-- Inventory --}}
                                <td>
                                    @if(!is_null($roomsToday))
                                        {{ $roomsToday }} rooms
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Price mismatches --}}
                                <td>
                                    {{ $mismatches }} mismatch{{ $mismatches == 1 ? '' : 'es' }}
                                </td>

                                {{-- Bookings (7d) --}}
                                <td>
                                    {{ $bookings7d }}
                                </td>

                                {{-- Status --}}
                                <td>
                                    <div class="{{ $statusClass }}">
                                        <span class="status-dot"></span>
                                        <span>{{ $statusLabel }}</span>
                                    </div>
                                </td>

                                {{-- Action: Pin / Unpin --}}
                                <td>
                                    @if($isPinned)
                                        <form method="POST"
                                              action="{{ route('admin.inventory.pinned.unpin', $row->id) }}"
                                              onsubmit="return confirm('Unpin this hotel?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inv-btn-pill inv-btn-unpin w-100">
                                                Unpin
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.inventory.pinned.pin', $hotel->id) }}">
                                            @csrf
                                            <button type="submit" class="inv-btn-pill inv-btn-pin w-100">
                                                Pin
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No pinned hotels found. Pin hotels from the Hotels List page to see them here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($pinnedHotels, 'links'))
                <div class="d-flex justify-content-between align-items-center mt-3" style="font-size:12px;">
                    <div class="text-muted">
                        @if($pinnedHotels->count())
                            Showing {{ $pinnedHotels->firstItem() }}–{{ $pinnedHotels->lastItem() }}
                            of {{ $pinnedHotels->total() }} pinned hotels
                        @else
                            No results
                        @endif
                    </div>
                    <div>
                        {{ $pinnedHotels->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
