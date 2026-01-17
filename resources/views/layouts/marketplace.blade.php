<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Travacot') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('marketplace.search.show') }}" class="text-xl font-semibold">Travacot Marketplace</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('marketplace.search.show') }}" class="hover:text-gray-700">Search</a>
                <a href="{{ route('marketplace.resales.index') }}" class="hover:text-gray-700">Resales</a>
                @auth
                    <span class="text-gray-500">{{ auth()->user()->name }}</span>
                @else
                    <a href="{{ route('login') }}" class="hover:text-gray-700">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
