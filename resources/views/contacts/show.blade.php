<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Contact Details</h2></x-slot>

    <div class="p-6 space-y-6">
        <x-flash />

        <x-panel>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="text-lg font-medium">{{ $contact->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-lg font-medium">{{ $contact->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Company</p>
                    <p class="text-lg font-medium">{{ $contact->company ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="text-lg font-medium">{{ $contact->phone ?? '—' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Notes</p>
                    <p class="mt-1 whitespace-pre-wrap">{{ $contact->notes ?? '—' }}</p>
                </div>
            </div>
        </x-panel>

        <div class="flex gap-3">
            @can('Edit Contact')
                <a href="{{ route('contacts.edit', $contact) }}" class="rounded bg-blue-600 px-4 py-2 text-white">Edit</a>
            @endcan
            @can('Delete Contact')
                <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                      onsubmit="return confirm('Delete this contact?')">
                    @csrf @method('DELETE')
                    <button class="rounded bg-red-600 px-4 py-2 text-white">Delete</button>
                </form>
            @endcan
            <a href="{{ route('contacts.index') }}" class="rounded bg-gray-200 px-4 py-2">Back</a>
        </div>
    </div>
</x-app-layout>
