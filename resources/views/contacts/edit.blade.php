<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Edit Contact: {{ $contact->name }}</h2></x-slot>

    <div class="p-6">
        <x-form-errors />
        <x-flash />

        <form action="{{ route('contacts.update', $contact) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')

            <x-panel title="Contact Details">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm">Name</span>
                        <input name="name" value="{{ old('name', $contact->name) }}" class="mt-1 w-full rounded border px-3 py-2"/>
                    </label>

                    <label class="block">
                        <span class="text-sm">Email</span>
                        <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="mt-1 w-full rounded border px-3 py-2"/>
                    </label>

                    <label class="block">
                        <span class="text-sm">Company</span>
                        <input name="company" value="{{ old('company', $contact->company) }}" class="mt-1 w-full rounded border px-3 py-2"/>
                    </label>

                    <label class="block">
                        <span class="text-sm">Phone</span>
                        <input name="phone" value="{{ old('phone', $contact->phone) }}" class="mt-1 w-full rounded border px-3 py-2"/>
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm">Notes</span>
                        <textarea name="notes" rows="4" class="mt-1 w-full rounded border px-3 py-2">{{ old('notes', $contact->notes) }}</textarea>
                    </label>
                </div>
            </x-panel>

            <div class="flex gap-2">
                <button class="rounded bg-blue-600 px-4 py-2 text-white">Save</button>
                <a href="{{ route('contacts.show', $contact) }}" class="rounded bg-gray-200 px-4 py-2">Back</a>
            </div>
        </form>
    </div>
</x-app-layout>
