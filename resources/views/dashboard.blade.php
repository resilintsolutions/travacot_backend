<x-app-layout>
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-slot name="header">
                    <h2 class="font-semibold text-xl">Dashboard</h2>
                </x-slot>

                <div class="p-6 space-y-6">
                    <p>Welcome, {{ auth()->user()->name }}!</p>

                    {{-- @role('admin')
                    <x-panel title="Admin Tools">
                        <ul class="list-disc pl-5">
                            <li><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
                            <li><a href="{{ route('admin.roles.index') }}">Manage Roles</a></li>
                            <li><a href="{{ route('admin.permissions.index') }}">Manage Permissions</a></li>
                            @can('Create Contact')<li><a href="{{ route('contacts.create') }}">New Contact</a></li>@endcan
                        </ul>
                    </x-panel>

                    @endrole

                    @can('view dashboard')
                    <x-panel title="General Widgets">
                        <p>Your widgets go here…</p>
                        @can('Create Contact')<li><a href="{{ route('contacts.create') }}">New Contact</a></li>@endcan
                    </x-panel>
                    @endcan --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>