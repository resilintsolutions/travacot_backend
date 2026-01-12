<x-app-layout>
    @php
        $search = $search ?? '';
        $filter = $filter ?? 'all';
    @endphp

    <div class="inv-page-wrapper">
        {{-- Breadcrumb --}}
        <div class="mb-2 text-muted" style="font-size: 12px;">
            Inventory <span class="mx-1">›</span> Content Health
        </div>

        {{-- Top Summary Card --}}
        <div class="health-summary-card mb-4">
            <div class="health-summary-title">Today's Summary Card</div>
            <div class="health-summary-subtitle">
                This section summarizes what is missing
            </div>

            <table class="health-summary-table">
                <tbody>
                    <tr>
                        <td class="health-summary-label">Missing Photos</td>
                        <td class="health-summary-value">{{ $summary['missing_photos'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label">Low-Quality Photos</td>
                        <td class="health-summary-value">{{ $summary['low_quality_photos'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label">Missing Descriptions</td>
                        <td class="health-summary-value">{{ $summary['missing_descriptions'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label">Missing Amenities</td>
                        <td class="health-summary-value">{{ $summary['missing_amenities'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label">Mapping Issues (Wrong coordinates / Wrong City)</td>
                        <td class="health-summary-value">{{ $summary['mapping_issues'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label">Outdated content</td>
                        <td class="health-summary-value">{{ $summary['outdated_content'] }}</td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                    <tr>
                        <td class="health-summary-label"><strong>Total Issues Today</strong></td>
                        <td class="health-summary-value"><strong>{{ $summary['total_issues'] }}</strong></td>
                        <td class="health-summary-trend">▲ Higher than yesterday</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Content Issues Card --}}
        <div class="inv-card">
            {{-- Header row --}}
            <div class="health-header-row mb-3">
                <div>
                    <div class="inv-header-title">Content Issues</div>
                    <div class="inv-page-title" style="margin-bottom: 8px;">Content Issues</div>
                    <div class="text-muted" style="font-size: 12px;">
                        See what issues specific hotels have
                    </div>

                    {{-- Tabs --}}
                    <div class="health-tabs">
                        <a href="{{ route('admin.inventory.content_health.index', ['filter' => 'all', 'search' => $search]) }}"
                           class="health-tab {{ $filter === 'all' ? 'health-tab--active' : '' }}">
                            All
                        </a>
                        <a href="{{ route('admin.inventory.content_health.index', ['filter' => 'issues', 'search' => $search]) }}"
                           class="health-tab {{ $filter === 'issues' ? 'health-tab--active' : '' }}">
                            Hotels with issues
                        </a>
                        <a href="{{ route('admin.inventory.content_health.index', ['filter' => 'critical', 'search' => $search]) }}"
                           class="health-tab {{ $filter === 'critical' ? 'health-tab--active' : '' }}">
                            Critical issues
                        </a>
                        <a href="{{ route('admin.inventory.content_health.index', ['filter' => 'warning', 'search' => $search]) }}"
                           class="health-tab {{ $filter === 'warning' ? 'health-tab--active' : '' }}">
                            Warning
                        </a>
                        <a href="{{ route('admin.inventory.content_health.index', ['filter' => 'healthy', 'search' => $search]) }}"
                           class="health-tab {{ $filter === 'healthy' ? 'health-tab--active' : '' }}">
                            Healthy
                        </a>
                    </div>
                </div>

                {{-- Search on the right --}}
                <form method="GET"
                      action="{{ route('admin.inventory.content_health.index') }}"
                      class="d-flex align-items-center"
                      style="gap:12px;">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="health-search-wrapper">
                        <span class="health-search-icon">🔍</span>
                        <input type="text"
                               name="search"
                               class="form-control health-search-input"
                               placeholder="Search Hotels"
                               value="{{ $search }}">
                    </div>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">
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
                            <th style="width: 10%;">Supplier</th>
                            <th style="width: 10%;">Score</th>
                            <th style="width: 11%;">Photos</th>
                            <th style="width: 11%;">Description</th>
                            <th style="width: 11%;">Amenities</th>
                            <th style="width: 14%;">Mapping</th>
                            <th style="width: 5%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($healthItems as $item)
                            @php
                                $hotel  = $item['hotel'];
                                $score  = $item['score'];
                                $photos = $item['photos'];
                                $desc   = $item['description'];
                                $amen   = $item['amenities'];
                                $map    = $item['mapping'];

                                // dot color for score
                                if ($score >= 90) {
                                    $dotColor = '#166534';
                                } elseif ($score >= 70) {
                                    $dotColor = '#f59e0b';
                                } else {
                                    $dotColor = '#b91c1c';
                                }
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

                                {{-- Score --}}
                                <td>
                                    <div class="d-flex align-items-center" style="gap:8px;">
                                        <span class="health-score-dot" style="background: {{ $dotColor }};"></span>
                                        <span>{{ $score }}%</span>
                                    </div>
                                </td>

                                {{-- Photos --}}
                                <td>
                                    @if($photos === 'missing')
                                        <span class="health-badge-bad">Missing</span>
                                    @elseif($photos === 'low')
                                        <span class="health-badge-warn">Low Quality</span>
                                    @else
                                        <span class="health-badge-ok">✔</span>
                                    @endif
                                </td>

                                {{-- Description --}}
                                <td>
                                    @if($desc === 'missing')
                                        <span class="health-badge-bad">Missing</span>
                                    @else
                                        <span class="health-badge-ok">✔</span>
                                    @endif
                                </td>

                                {{-- Amenities --}}
                                <td>
                                    @if($amen === 'missing')
                                        <span class="health-badge-bad">Missing</span>
                                    @else
                                        <span class="health-badge-ok">✔</span>
                                    @endif
                                </td>

                                {{-- Mapping --}}
                                <td>
                                    @if($map === 'wrong')
                                        <span class="health-badge-warn">Wrong Destination</span>
                                    @elseif($map === 'missing')
                                        <span class="health-badge-bad">Missing Mapping</span>
                                    @else
                                        <span class="health-badge-ok">✔</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td>
                                    <a href="{{ route('admin.hotels.show', $hotel->id ?? 0) }}"
                                       class="btn btn-sm btn-light border rounded-pill px-3"
                                       style="font-size: 11px;">
                                        View Details →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No hotels found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
