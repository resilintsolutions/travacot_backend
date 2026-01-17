@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Search Hotels</h1>

    <form method="POST" action="{{ route('marketplace.search.perform') }}" class="grid gap-4 md:grid-cols-2 bg-white p-6 rounded-lg shadow-sm">
        @csrf
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">City / Country / Street</label>
            <input name="query" type="text" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('query') }}">
            @error('query')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Check-in</label>
            <input name="check_in" type="date" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('check_in') }}">
        </div>
        <div>
            <label class="block text-sm font-medium">Check-out</label>
            <input name="check_out" type="date" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('check_out') }}">
        </div>
        <div>
            <label class="block text-sm font-medium">Adults</label>
            <input name="adults" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('adults', 2) }}">
        </div>
        <div>
            <label class="block text-sm font-medium">Children</label>
            <input name="children" type="number" min="0" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('children', 0) }}">
        </div>
        <div class="md:col-span-2">
            <button class="px-5 py-2 bg-indigo-600 text-white rounded-md">Search</button>
        </div>
    </form>
@endsection
