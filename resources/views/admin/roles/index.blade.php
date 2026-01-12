<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="text-xl font-semibold">Roles</h2>
                </x-slot>

                <div class="p-6">
                    <x-flash />
                    <div class="mb-4 flex items-center justify-between">
                        <form method="GET">
                            <input name="q" value="{{ request('q') }}" placeholder="Search role…"
                                class="rounded border px-3 py-2" />
                            <button class="rounded bg-blue-600 px-4 py-2 text-white">Search</button>
                        </form>
                        <a href="{{ route('admin.roles.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New Role</a>
                    </div>

                    <x-panel>
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Permissions</th>
                                    <th class="py-2 w-40"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                <tr class="border-b">
                                    <td class="py-2">{{ $role->name }}</td>
                                    <td class="py-2 text-sm">
                                        {{ $role->permissions->pluck('name')->join(', ') ?: '—' }}
                                    </td>
                                    <td class="py-2 d-flex gap-1">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-inverse-info btn-fw">Edit</a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this role?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-inverse-danger btn-fw">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $roles->links() }}
                        </div>
                    </x-panel>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>