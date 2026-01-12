<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="text-xl font-semibold">Create User</h2>
                </x-slot>

                <div class="p-6">
                    <x-form-errors />

                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <x-panel title="User Details">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="text-sm">Name</span>
                                    <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border px-3 py-2" />
                                </label>

                                <label class="block">
                                    <span class="text-sm">Email</span>
                                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded border px-3 py-2" />
                                </label>

                                <label class="block">
                                    <span class="text-sm">Password</span>
                                    <input type="password" name="password" class="mt-1 w-full rounded border px-3 py-2" />
                                </label>

                                <label class="block">
                                    <span class="text-sm">Confirm Password</span>
                                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded border px-3 py-2" />
                                </label>
                            </div>
                        </x-panel>

                        <x-panel title="Assign Roles">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                @foreach ($roles as $role)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                        @checked(in_array($role->name, old('roles', [])))>
                                    <span>{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </x-panel>

                        <x-panel title="Direct Permissions (optional)">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                @foreach ($permissions as $perm)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                        @checked(in_array($perm->name, old('permissions', [])))>
                                    <span>{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Tip: Prefer granting via roles unless you need a one-off.</p>
                        </x-panel>

                        <div class="flex gap-2">
                            <button class="rounded bg-blue-600 px-4 py-2 text-white">Create</button>
                            <a href="{{ route('admin.users.index') }}" class="rounded bg-gray-200 px-4 py-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>