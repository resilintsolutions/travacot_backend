@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Marketplace Resales</h1>

    <div class="grid gap-4">
        @forelse ($resales as $resale)
            <div class="bg-white p-5 rounded-lg shadow-sm">
                <h2 class="text-lg font-semibold">
                    {{ $resale->reservation->hotel->name ?? 'Hotel' }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $resale->listed_price }} {{ $resale->currency }}
                </p>
                <a href="{{ route('marketplace.resales.show', $resale) }}" class="text-indigo-600 text-sm mt-2 inline-block">View resale</a>
            </div>
        @empty
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p>No resales available right now.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $resales->links() }}
    </div>
@endsection
