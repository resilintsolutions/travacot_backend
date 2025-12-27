<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Contacts</h2>
    </x-slot>

    <div class="p-6">
        <x-flash />

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input name="q" value="{{ request('q') }}" placeholder="Search name / email / company"
                       class="w-72 rounded border px-3 py-2"/>
                <button class="rounded bg-gray-800 px-3 py-2 text-white">Search</button>
            </form>

            @can('Create Contact')
                <a href="{{ route('contacts.create') }}" class="rounded bg-blue-600 px-3 py-2 text-white">New Contact</a>
            @endcan
        </div>

        <x-panel>
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Name</th>
                        <th class="py-2">Email</th>
                        <th class="py-2">Company</th>
                        <th class="py-2">Phone</th>
                        <th class="py-2 w-48 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contacts as $c)
                        <tr class="border-b">
                            <td class="py-2">{{ $c->name }}</td>
                            <td class="py-2">{{ $c->email ?? '—' }}</td>
                            <td class="py-2">{{ $c->company ?? '—' }}</td>
                            <td class="py-2">{{ $c->phone ?? '—' }}</td>
                            <td class="py-2 text-right space-x-3">
                                @can('Read Contact')
                                    <a href="{{ route('contacts.show', $c) }}" class="text-gray-700 hover:underline">View</a>
                                @endcan
                                @can('Edit Contact')
                                    <a href="{{ route('contacts.edit', $c) }}" class="text-blue-600 hover:underline">Edit</a>
                                @endcan
                                @can('Delete Contact')
                                    <form action="{{ route('contacts.destroy', $c) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this contact?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">No contacts found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">{{ $contacts->links() }}</div>
        </x-panel>
    </div>
</x-app-layout>
