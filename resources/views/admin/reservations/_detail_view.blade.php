<style>
    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-failed { background: #fff1f1; color: #b60000; border: 1px solid #b60000; }
    .status-pending { background: #fffce5; color: #7a6b00; border: 1px solid #d1c200; }
    .status-confirmed { background: #e9fff1; color: #0f7a39; border: 1px solid #0f7a39; }
    .status-cancelled { background: #ffeceb; color: #d30000; border: 1px solid #d30000; }
    .status-modified { background: #eef1ff; color: #3c49ff; border: 1px solid #3c49ff; }

    .icon-arrow {
        font-weight: bold;
        display: inline-block;
        margin: 0 8px;
    }

    .action-btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
    }

    .section-block {
        margin-bottom: 48px;
    }
</style>

@php
    $status = strtolower($reservation->status);
@endphp

<div class="p-2">

    <h6 class="fw-bold mb-3">Reservation Details</h6>
    <hr>

    {{-- ===================================================== --}}
    {{-- FAILED BLOCK --}}
    {{-- ===================================================== --}}
    @if(in_array($status, ['failed', 'failed_booking', 'payment_failed']))
        <div class="section-block">

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-1">{{ $reservation->customer_name }}</h6>
                <span class="status-badge status-failed">Failed</span>
            </div>

            <div class="text-muted mb-3">{{ $reservation->hotel->name }}</div>

            <div>
                <strong>Check-In</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_in }}
            </div>

            <div class="d-flex justify-content-between">
                <div>
                    <strong>Check-Out</strong>
                    <span class="icon-arrow">→</span>
                    {{ $reservation->check_out }}
                </div>
                <div>
                    Auto-retries
                    <span class="icon-arrow">→</span>
                    10
                </div>
            </div>

            <div class="mt-2">
                <strong>Refund Status</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->refund_status ?? 'N/A' }}
            </div>

            <div class="mt-2">
                <strong>Error Summary</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->failure_reason ?? 'Rate not available / API error' }}
            </div>

            <button
                class="btn btn-primary action-btn btn-retry mt-3"
                type="button"
                data-id="{{ $reservation->id }}">
                <i class="bi bi-arrow-repeat"></i> Retry Booking
            </button>

            <div class="text-muted mt-2">
                Reservation switches from pending to failed after retry attempts.
            </div>

        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- PENDING BLOCK --}}
    {{-- ===================================================== --}}
    @if($status === 'pending')
        <div class="section-block">

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-1">{{ $reservation->customer_name }}</h6>
                <span class="status-badge status-pending">Pending</span>
            </div>

            <div class="text-muted mb-3">{{ $reservation->hotel->name }}</div>

            <div>
                <strong>Check-In</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_in }}
            </div>

            <div class="d-flex justify-content-between">
                <div>
                    <strong>Check-Out</strong>
                    <span class="icon-arrow">→</span>
                    {{ $reservation->check_out }}
                </div>
                <div>
                    Auto-retries
                    <span class="icon-arrow">→</span>
                    7
                </div>
            </div>

            <div class="mt-2">
                Next retry:
                {{ $reservation->next_retry_at ?? '—' }}
            </div>

            <div class="mt-2">
                <strong>Refund</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->refund_status ?? 'Captured' }}
            </div>

            <div class="mt-2">
                <strong>Error Summary</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->failure_reason ?? 'Waiting for supplier confirmation' }}
            </div>

            <button
                class="btn btn-dark action-btn btn-check-status mt-3"
                type="button"
                data-id="{{ $reservation->id }}">
                <i class="bi bi-search"></i> Check Status
            </button>

        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- CONFIRMED BLOCK --}}
    {{-- ===================================================== --}}
    @if($status === 'confirmed')
        <div class="section-block">

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-1">{{ $reservation->customer_name }}</h6>
                <span class="status-badge status-confirmed">Confirmed</span>
            </div>

            <div class="text-muted mb-3">{{ $reservation->hotel->name }}</div>

            <div>
                <strong>Check-In</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_in }}
            </div>

            <div>
                <strong>Check-Out</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_out }}
            </div>

            @if($reservation->isCancellable())
                <button
                    class="btn btn-danger action-btn btn-cancel-booking mt-3"
                    type="button"
                    data-id="{{ $reservation->id }}">
                    Cancel Booking
                </button>
            @else
                <div class="text-muted small mt-2">
                    Non-refundable / cancellation window expired
                </div>
            @endif

            @php
                $policies = collect(data_get($reservation->raw_response, 'hotel.rooms', []))
                    ->flatMap(fn ($r) => $r['rates'] ?? [])
                    ->flatMap(fn ($r) => $r['cancellationPolicies'] ?? []);
            @endphp

            @if($policies->isNotEmpty())
                <div class="mt-2 small text-muted">
                    @foreach($policies as $p)
                        Cancellation from {{ $p['from'] ?? '-' }}
                        — Penalty: {{ $p['amount'] ?? 'N/A' }} {{ $p['currency'] ?? '' }}<br>
                    @endforeach
                </div>
            @endif

        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- CANCELLED BLOCK --}}
    {{-- ===================================================== --}}
    @if($status === 'cancelled')
        <div class="section-block">

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-1">{{ $reservation->customer_name }}</h6>
                <span class="status-badge status-cancelled">Cancelled</span>
            </div>

            <div class="text-muted mb-3">{{ $reservation->hotel->name }}</div>

            <div>
                <strong>Check-In</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_in }}
            </div>

            <div>
                <strong>Check-Out</strong>
                <span class="icon-arrow">→</span>
                {{ $reservation->check_out }}
            </div>

        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- MODIFIED BLOCK --}}
    {{-- ===================================================== --}}
    @if($status === 'modified')
        <div class="section-block">

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-1">{{ $reservation->customer_name }}</h6>
                <span class="status-badge status-modified">Modified</span>
            </div>

            <div class="text-muted mb-3">{{ $reservation->hotel->name }}</div>

            <div class="row">
                <div class="col-md-5">
                    <strong>Old</strong>
                    <div>Check-In <span class="icon-arrow">→</span> {{ $reservation->original_check_in }}</div>
                    <div>Check-Out <span class="icon-arrow">→</span> {{ $reservation->original_check_out }}</div>
                </div>

                <div class="col-md-2 text-center" style="font-size: 28px;">➡️</div>

                <div class="col-md-5 text-end">
                    <strong>New</strong>
                    <div>Check-In <span class="icon-arrow">→</span> {{ $reservation->check_in }}</div>
                    <div>Check-Out <span class="icon-arrow">→</span> {{ $reservation->check_out }}</div>
                </div>
            </div>

            <button
                class="btn btn-info action-btn btn-rebook mt-3"
                type="button"
                data-id="{{ $reservation->id }}">
                <i class="bi bi-arrow-repeat"></i> Rebook
            </button>

            <div class="text-muted mt-2">
                If possible it shows. If not then the button disappears.
            </div>

        </div>
    @endif

</div>
