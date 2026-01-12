<x-app-layout>
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4 flex items-center justify-between">
                        <form method="GET">
                            <input name="q" value="{{ request('q') }}" placeholder="Search name/email…"
                                class="rounded border px-3 py-2" />
                            <button class="rounded bg-blue-600 px-4 py-2 text-white">Search</button>
                        </form>
                        <a href="{{ route('admin.users.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New User</a>
                    </div>
                    <h4 class="card-title">All Users</h4>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Email</th>
                                    <th class="py-2">Roles</th>
                                    <th class="py-2">Direct Perms</th>
                                    <th class="py-2 w-40"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $u)
                                <tr class="border-b">
                                    <td class="py-2">{{ $u->name }}</td>
                                    <td class="py-2">{{ $u->email }}</td>
                                    <td class="py-2 text-sm">{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2 text-sm">{{ $u->permissions->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-inverse-info btn-fw">Edit</a>
                                        @if(auth()->id() !== $u->id)
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-inverse-danger btn-fw">Delete</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>