@extends('layouts.marketplace')

@section('content')
    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h1 class="text-2xl font-semibold">{{ $hotel['name'] }}</h1>
            <p class="text-sm text-gray-500">{{ $hotel['address'] }}, {{ $hotel['cityName'] }}, {{ $hotel['countryName'] }}</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach (array_slice($hotel['images'], 0, 4) as $img)
                    <img src="{{ $img }}" class="w-full h-48 object-cover rounded-md" alt="Hotel image">
                @endforeach
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold">Description</h2>
                <p class="text-sm text-gray-600 mt-2">{{ $hotel['description'] ?? 'No description provided.' }}</p>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold">Amenities</h2>
                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600">
                    @foreach ($hotel['amenities'] as $amenity)
                        <span class="px-2 py-1 bg-gray-100 rounded-md">{{ $amenity }}</span>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold">House Rules</h2>
                <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                    @forelse ($hotel['houseRules'] as $rule)
                        <li>{{ $rule }}</li>
                    @empty
                        <li>No house rules provided.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Available Rooms</h2>
            <div class="space-y-4">
                @foreach ($rooms as $room)
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium">{{ $room['name'] }}</h3>
                        <p class="text-xs text-gray-500">From {{ number_format($room['pricePerNight'], 2) }} per night</p>

                        <div class="mt-3 space-y-3">
                            @foreach ($room['rates'] as $rate)
                                <form method="POST" action="{{ route('marketplace.checkout.start') }}" class="border rounded-md p-3">
                                    @csrf
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-medium">{{ $rate['board'] ?? 'Room only' }}</p>
                                            <p class="text-xs text-gray-500">{{ $rate['refundable'] ? 'Refundable' : 'Non-refundable' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold">{{ number_format($rate['pricing']['final_price'], 2) }} {{ $rate['currency'] }}</p>
                                            @if (!empty($rate['offers']))
                                                <p class="text-xs text-indigo-600">Discounted</p>
                                            @endif
                                        </div>
                                    </div>

                                    <input type="hidden" name="hotel_code" value="{{ $hotel['code'] }}">
                                    <input type="hidden" name="rate_key" value="{{ $rate['rate_key'] }}">
                                    <input type="hidden" name="net" value="{{ $rate['net'] }}">
                                    <input type="hidden" name="currency" value="{{ $rate['currency'] }}">
                                    <input type="hidden" name="check_in" value="{{ $search['check_in'] }}">
                                    <input type="hidden" name="check_out" value="{{ $search['check_out'] }}">
                                    <input type="hidden" name="adults" value="{{ $search['adults'] }}">
                                    <input type="hidden" name="children" value="{{ $search['children'] }}">
                                    <input type="hidden" name="cancellationPolicies" value='@json($rate["cancellationPolicies"])'>

                                    <button class="mt-3 w-full bg-indigo-600 text-white py-2 rounded-md text-sm">
                                        Select and continue
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
