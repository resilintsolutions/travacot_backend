@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border p-4 shadow-sm']) }}>
    @if($title)
        <h3 class="mb-3 text-lg font-semibold">{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
