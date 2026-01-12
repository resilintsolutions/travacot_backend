@if (session('status'))
    <div class="mb-4 rounded-lg border p-3 text-sm bg-green-50">
        {{ session('status') }}
    </div>
@endif
