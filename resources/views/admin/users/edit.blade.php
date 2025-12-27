<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="text-xl font-semibold">Edit User: {{ $user->name }}</h2>
                </x-slot>

                <div class="p-6">
                    <x-form-errors />
                    <x-flash />

                    <x-panel title="Details" class="mb-6">
                        <dl class="grid grid-cols-1 gap-2 md:grid-cols-3">
                            <div>
                                <dt class="text-sm text-gray-600">Name</dt>
                                <dd>{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Email</dt>
                                <dd>{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Current Roles</dt>
                                <dd>{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </x-panel>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                        @csrf @method('PUT')

                        <x-panel title="Assign Roles">
                            @php $assignedRoles = $user->roles->pluck('name')->all(); @endphp
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                @foreach ($roles as $role)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                        @checked(in_array($role->name, old('roles', $assignedRoles)))>
                                    <span>{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </x-panel>

                        <x-panel title="Direct Permissions (optional)">
                            @php $assignedPerms = $user->permissions->pluck('name')->all(); @endphp
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                @foreach ($permissions as $perm)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                        @checked(in_array($perm->name, old('permissions', $assignedPerms)))>
                                    <span>{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Tip: Prefer granting via roles unless you need a one-off.</p>
                        </x-panel>

                        <div class="flex gap-2">
                            <button class="rounded bg-blue-600 px-4 py-2 text-white">Save</button>
                            <a href="{{ route('admin.users.index') }}" class="rounded bg-gray-200 px-4 py-2">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>