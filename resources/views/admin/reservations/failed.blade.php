<x-app-layout>
    <div class="container-fluid py-4">

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Failed Bookings</h4>
                    <small class="text-muted">
                        View all reservations (ongoing + cancelled + pending)
                    </small>
                </div>
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
                        <label class="form-label">Destination</label>
                        <input type="text"
                               name="destination"
                               value="{{ request('destination') }}"
                               class="form-control"
                               placeholder="Destination Code">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date"
                               name="start_date"
                               value="{{ request('start_date') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date"
                               name="end_date"
                               value="{{ request('end_date') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Guest name, reservation number, supplier confirmation">
                    </div>

                    <div class="col-md-12 d-flex gap-2">
                        <button class="btn btn-dark px-4">
                            Search
                        </button>

                        <a href="{{ route('admin.reservations.failed') }}"
                           class="btn btn-light border px-4">
                            Reset Filters
                        </a>
                    </div>

                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Guest Name</th>
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
                                @php
                                    $supplierCost = $r->guest_info['supplier_total_net'] ?? 0;
                                @endphp

                                <tr>
                                    <td>{{ $r->customer_name }}</td>
                                    <td>{{ $r->hotel?->name ?? '-' }}</td>
                                    <td>{{ $r->hotel?->vendor ?? '-' }}</td>
                                    <td>{{ $r->confirmation_number ?? '-' }}</td>
                                    <td>{{ $r->supplier_reference ?? '-' }}</td>
                                    <td>{{ $r->check_in }}</td>
                                    <td>{{ $r->check_out }}</td>
                                    <td>{{ $r->booking_channel ?? 'Website' }}</td>
                                    <td>${{ number_format($r->total_price, 2) }}</td>
                                    <td>${{ number_format($supplierCost, 2) }}</td>
                                    <td>
                                        ${{ number_format($r->total_price - $supplierCost, 2) }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td>
                                        <span class="badge rounded-pill text-danger border border-danger px-3">
                                            Failed
                                        </span>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="d-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="viewReservation({{ $r->id }})">
                                            View more →
                                        </button>

                                        @if(in_array($r->status, ['failed', 'failed_booking', 'payment_failed']))
                                            <button
                                                class="btn btn-outline-danger btn-sm btn-retry"
                                                data-id="{{ $r->id }}">
                                                Retry
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        No failed bookings found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reservations->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </div>

    {{-- RESERVATION DETAIL MODAL --}}
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

    {{-- JS --}}
    <script>
        function viewReservation(id) {
            $('#reservationDetailModal').modal('show');
            $('#reservationDetailsContent').html('<div class="text-center py-5">Loading...</div>');

            $.get(`${APP_URL}/admin/reservations/${id}`, function(res) {
                $('#reservationDetailsContent').html(res.html);
            });
        }

        function ajaxAction(url, id, actionName) {

            let btn = $(`button[data-id="${id}"].btn-${actionName}`);
            btn.prop("disabled", true).text("Processing...");

            $.ajax({
                url: url,
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Something went wrong');
                    btn.prop("disabled", false).text("Retry");
                }
            });
        }

        $(document).on("click", ".btn-retry", function() {
            let id = $(this).data("id");
            ajaxAction(`${APP_URL}/admin/reservations/${id}/retry`, id, "retry");
        });
    </script>

</x-app-layout>
