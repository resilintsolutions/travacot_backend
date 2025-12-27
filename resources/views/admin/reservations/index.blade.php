<x-app-layout>
    <div class="container-fluid py-4">

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">All Reservations</h4>
            </div>

            <div class="card-body">

                {{-- FILTERS --}}
                <form method="GET" class="row g-3 mb-4">

                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier" class="form-select">
                            <option value="">Select Supplier</option>
                            <option value="hotelbeds" {{ request('supplier') == 'hotelbeds' ? 'selected' : '' }}>
                                Hotelbeds
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Destination Code</label>
                        <input type="text" name="destination" value="{{ request('destination') }}"
                            class="form-control" placeholder="Ex: DXB">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All</option>
                            <option value="succeeded" {{ request('payment_status') == 'succeeded' ? 'selected' : '' }}>
                                Succeeded</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Guest, Res #, Supplier Ref">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>

                    <div class="col-md-12 d-flex gap-2">
                        <button class="btn btn-dark px-4">Apply Filters</button>
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-light border px-4">Reset</a>
                    </div>

                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Guest</th>
                                <th>Hotel</th>
                                <th>Supplier</th>
                                <th>Reservation Number</th>
                                <th>Supplier Confirmation (If available)</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Booking Channel</th>
                                <th>Selling Price</th>
                                <th>Cost</th>
                                <th>Margin</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($reservations as $r)
                                <tr>
                                    <td>{{ $r->customer_name }}</td>
                                    <td>{{ $r->hotel?->name }}</td>
                                    <td>{{ $r->hotel?->vendor }}</td>
                                    <td>{{ $r->confirmation_number }}</td>
                                    <td>{{ $r->supplier_reference }}</td>
                                    <td>{{ $r->check_in }}</td>
                                    <td>{{ $r->check_out }}</td>
                                    <td>{{ $r->booking_channel }}</td>
                                    <td>${{ number_format($r->total_price, 2) }}</td>
                                    <td>${{ number_format($r->guest_info['supplier_total_net'] ?? 0, 2) }}</td>
                                    <td>${{ number_format($r->total_price - ($r->guest_info['supplier_total_net'] ?? 0), 2) }}
                                    </td>

                                    {{-- STATUS BADGES --}}
                                    <td>
                                        @php
                                            $status = strtolower($r->status);
                                        @endphp

                                        @if ($status === 'confirmed')
                                            <span class="badge bg-success">Confirmed</span>
                                        @elseif($status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @elseif($status === 'failed' || $status === 'payment_failed')
                                            <span class="badge bg-dark">Failed</span>
                                        @elseif($status === 'modified')
                                            <span class="badge bg-primary">Modified</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="viewReservation({{ $r->id }})">
                                            View more →
                                        </button>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">No reservations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reservations->links() }}
                </div>

            </div>
        </div>

    </div>
    {{-- GLOBAL RESERVATION DETAIL MODAL --}}
    <div class="modal fade" id="reservationDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Reservation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="reservationDetailsContent">
                    <div class="text-center py-5">
                        Loading...
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
