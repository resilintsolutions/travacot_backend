@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Processing your booking</h1>
    <p class="text-sm text-gray-600">We’re confirming your reservation. This may take a few moments.</p>

    <div id="status" class="mt-6 text-sm text-gray-700">Checking status...</div>

    <script>
        const reservationId = {{ $reservation->id }};
        const statusUrl = '{{ route('marketplace.reservations.status', $reservation) }}';

        const poll = async () => {
            const resp = await fetch(statusUrl);
            const data = await resp.json();
            document.getElementById('status').textContent = 'Status: ' + data.status;

            if (data.status === 'confirmed') {
                window.location.href = '{{ route('marketplace.checkout.confirmation', $reservation) }}';
            } else {
                setTimeout(poll, 3000);
            }
        };

        poll();
    </script>
@endsection
