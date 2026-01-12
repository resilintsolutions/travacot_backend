<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="text-xl font-semibold">Permissions</h2>
                </x-slot>
                <div class="p-6">
                    <x-flash />
                    <div class="mb-4 flex items-center justify-between">
                        <div></div>
                        <a href="{{ route('admin.permissions.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">New Permission</a>
                    </div>

                    <x-panel>
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Name</th>
                                    <th class="py-2 w-40"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $perm)
                                <tr class="border-b">
                                    <td class="py-2">{{ $perm->name }}</td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}"
                                            onsubmit="return confirm('Delete this permission?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-inverse-danger btn-fw">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $permissions->links() }}
                        </div>
                    </x-panel>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>