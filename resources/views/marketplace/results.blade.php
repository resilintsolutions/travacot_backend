@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-2">Available Hotels</h1>
    <p class="text-sm text-gray-500 mb-6">
        {{ $query['query'] }} · {{ $query['check_in'] }} to {{ $query['check_out'] }}
    </p>

    @if (empty($results))
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <p>No hotels found for this search. Try another location.</p>
        </div>
    @else
        <div class="grid gap-6">
            @foreach ($results as $hotel)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden md:flex">
                    <div class="md:w-1/3">
                        <img src="{{ $hotel['images'][0] ?? '' }}" alt="{{ $hotel['name'] }}" class="w-full h-48 object-cover">
                    </div>
                    <div class="p-5 flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $hotel['name'] }}</h2>
                                <p class="text-sm text-gray-500">{{ $hotel['cityName'] }}, {{ $hotel['countryName'] }}</p>
                            </div>
                            <div class="text-right">
                                @if (!$includeDiscounted && $hotel['isDiscounted'])
                                    <p class="text-sm text-indigo-600">Login to see discounted rate</p>
                                @else
                                    <p class="text-lg font-semibold">{{ number_format($hotel['lowestRate'], 2) }} {{ $hotel['currency'] }}</p>
                                    <p class="text-xs text-gray-500">per stay</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-600">
                            <span>Refundable: {{ $hotel['refundable'] ? 'Yes' : 'No' }}</span>
                            <span>Meal Included: {{ $hotel['mealIncluded'] ? 'Yes' : 'No' }}</span>
                            <span>Rooms Left: {{ $hotel['roomsLeft'] ?? 'N/A' }}</span>
                        </div>
                        <div class="mt-4">
                            <a class="text-indigo-600 text-sm font-medium" href="{{ route('marketplace.hotels.show', [
                                'hotelCode' => $hotel['code'],
                                'check_in' => $query['check_in'],
                                'check_out' => $query['check_out'],
                                'adults' => $query['adults'],
                                'children' => $query['children'],
                            ]) }}">
                                View details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
