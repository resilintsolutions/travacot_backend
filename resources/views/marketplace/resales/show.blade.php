@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Resale Details</h1>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="text-sm text-gray-700 space-y-1">
            <div>Hotel: {{ $resale->reservation->hotel->name ?? 'Hotel' }}</div>
            <div>Price: {{ number_format($resale->listed_price, 2) }} {{ $resale->currency }}</div>
            <div>Status: {{ ucfirst($resale->status) }}</div>
        </div>

        @auth
            @if ($resale->status === 'listed')
                <form method="POST" action="{{ route('marketplace.resales.buy', $resale) }}" class="mt-4">
                    @csrf
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-md">Buy resale</button>
                </form>
            @endif
        @else
            <p class="text-sm text-gray-600 mt-4">Login to purchase this resale.</p>
        @endauth
    </div>
@endsection
