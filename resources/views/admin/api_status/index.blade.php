<x-app-layout>
    <x-slot name="title">API Status Cards</x-slot>

    {{-- Shared inventory / analytics styles --}}
    {{-- If you already moved these to a CSS file, remove this <style> and include the file instead --}}
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
        .inv-search-input {
            border-radius: 999px;
            padding-left: 40px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .inv-search-input:focus {
            box-shadow: 0 0 0 1px #4f46e5;
            border-color: #4f46e5;
        }
        .inv-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }
        .inv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .inv-table th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            padding: 10px 14px;
            border-bottom: 1px solid #e5e7eb;
            background: transparent;
            white-space: nowrap;
        }
        .inv-table tbody tr {
            background: #f9fafb;
        }
        .inv-table tbody tr td {
            font-size: 13px;
            padding: 12px 14px;
            border-top: 1px solid transparent;
            border-bottom: 1px solid transparent;
            vertical-align: middle;
        }
        .inv-table tbody tr td:first-child {
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }
        .inv-table tbody tr td:last-child {
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }
        .pill-success {
            background: #ecfdf5;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .pill-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .pill-muted {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }
        .status-pill--active {
            background: #ecfdf5;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .status-pill--inactive {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .status-pill--unknown {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }
        .inv-tab {
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 999px;
            border: none;
            background: transparent;
            color: #6b7280;
        }
        .inv-tab.active {
            background: #111827;
            color: #ffffff;
        }
        .inv-table-wrapper {
            margin-top: 18px;
            overflow-x: auto;
        }
    </style>

    @php
        // Allow $suppliers or $rows from controller
        $rows = $rows ?? ($suppliers ?? collect());

        $filter = request('status'); // all | active | inactive
        $filtered = collect($rows)->filter(function($row) use ($filter) {
            if (!$filter || $filter === 'all') return true;
            if ($filter === 'active')   return ($row['api_status'] ?? '') === 'Active';
            if ($filter === 'inactive') return ($row['api_status'] ?? '') === 'Inactive';
            return true;
        });
    @endphp

    <div class="mb-3 text-xs text-gray-500">
        Home <span class="mx-1">›</span> API Status Cards
    </div>

    <div class="container-fluid py-4">
        <div class="inv-card mb-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <div class="inv-header-title">API Status Cards</div>
                    <div class="inv-page-title">Partners & Supplier Health</div>
                </div>

                {{-- Search + tabs --}}
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">

                    {{-- Tabs --}}
                    <div class="btn-group" role="group" aria-label="Filter by status">
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}"
                           class="inv-tab {{ !$filter || $filter === 'all' ? 'active' : '' }}">
                            All
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive']) }}"
                           class="inv-tab {{ $filter === 'inactive' ? 'active' : '' }}">
                            Deactivated
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}"
                           class="inv-tab {{ $filter === 'active' ? 'active' : '' }}">
                            Activated
                        </a>
                    </div>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('admin.api-status.index') }}" class="position-relative">
                        @if($filter)
                            <input type="hidden" name="status" value="{{ $filter }}">
                        @endif
                        <span class="inv-search-icon">
                            <i class="mdi mdi-magnify"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control inv-search-input"
                            placeholder="Search Partner"
                            value="{{ request('search') }}"
                            style="min-width: 260px;"
                        >
                    </form>
                </div>
            </div>

            <div class="inv-table-wrapper">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Active Hotels</th>
                            <th>Inactive Hotels</th>
                            <th>Funneled Hotels</th>
                            <th>Total Inventory</th>
                            <th>Top Demand Rooms</th>
                            <th>Most Booked<br><span class="inv-subtitle">From / To</span></th>
                            <th>Status</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($filtered as $row)
                        @php
                            $status = $row['api_status'] ?? 'Unknown';
                            $statusClass = match($status) {
                                'Active'   => 'status-pill--active',
                                'Inactive' => 'status-pill--inactive',
                                default    => 'status-pill--unknown',
                            };
                        @endphp
                        <tr>
                            {{-- Partner name --}}
                            <td>
                                <div class="fw-semibold">{{ $row['partner'] ?? $row['code'] }}</div>
                                <div class="inv-subtitle">{{ $row['code'] }}</div>
                            </td>

                            <td class="text-success fw-semibold">
                                {{ number_format($row['active_hotels'] ?? 0) }}
                            </td>

                            <td class="text-danger fw-semibold">
                                {{ number_format($row['inactive_hotels'] ?? 0) }}
                            </td>

                            <td class="text-muted">
                                {{ number_format($row['funneled_hotels'] ?? 0) }}
                            </td>

                            <td class="text-muted">
                                {{ number_format($row['total_inventory'] ?? 0) }}
                            </td>

                            <td class="text-muted">
                                {{ $row['top_demand_room'] ?? '-' }}
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-muted" style="font-size:12px;">From</span>
                                    <span class="fw-semibold" style="font-size:13px;">
                                        {{ $row['most_booked_from'] ?? '-' }}
                                    </span>
                                    <span class="text-muted mt-1" style="font-size:12px;">To</span>
                                    <span class="fw-semibold" style="font-size:13px;">
                                        {{ $row['most_booked_to'] ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="status-pill {{ $statusClass }}">
                                    <span class="status-dot"></span>
                                    {{ $status }}
                                    @if(!empty($row['api_status_code']))
                                        <span class="ms-1" style="font-size:10px;">
                                            ({{ $row['api_status_code'] }})
                                        </span>
                                    @endif
                                </span>
                            </td>

                            <td style="text-align:right;">
                                @if(!empty($row['id']))
                                    <form method="POST"
                                          action="{{ route('admin.api-status.toggle', $row['id']) }}"
                                          style="display:inline;">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ ($status === 'Active') ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                                            {{ $status === 'Active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:12px;">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="text-center text-muted py-4">
                                    No suppliers found. Import some hotels or make calls to suppliers
                                    so <code>supplier_responses</code> has data.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
