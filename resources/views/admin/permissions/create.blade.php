<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="text-xl font-semibold">Create Permission</h2>
                </x-slot>
                <div class="p-6">
                    <x-form-errors />
                    <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <x-panel title="Details">
                            <label class="block">
                                <span class="text-sm">Name</span>
                                <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border px-3 py-2" />
                            </label>
                        </x-panel>

                        <div class="flex gap-2">
                            <button class="rounded bg-blue-600 px-4 py-2 text-white">Create</button>
                            <a href="{{ route('admin.permissions.index') }}" class="rounded bg-gray-200 px-4 py-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>