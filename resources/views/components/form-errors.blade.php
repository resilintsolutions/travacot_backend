@if ($errors->any())
    <div class="mb-4 rounded-lg border p-3 text-sm bg-red-50">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
