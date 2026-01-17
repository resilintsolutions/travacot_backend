@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Booking Confirmed</h1>
    <p class="text-sm text-gray-600">Your reservation is confirmed.</p>

    <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold">Reservation Details</h2>
        <div class="mt-3 text-sm text-gray-700 space-y-1">
            <div>Reservation ID: {{ $reservation->id }}</div>
            <div>Status: {{ ucfirst($reservation->status) }}</div>
            <div>Total: {{ number_format($reservation->total_price, 2) }} {{ $reservation->currency }}</div>
        </div>
    </div>
@endsection
