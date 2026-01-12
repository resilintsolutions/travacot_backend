<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $mode === 'create' ? 'Add MSP Rule' : 'Edit MSP Rule' }}
                </h2>
            </div>

            <div>
                <a href="{{ route('admin.msp.index') }}" class="btn btn-secondary">Back to MSP list</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ $mode === 'create' ? route('admin.msp.store') : route('admin.msp.update', $msp) }}">
                    @csrf
                    @if($mode === 'edit') @method('PUT') @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scope</label>
                            <select id="msp-scope" name="scope" class="mt-1 block w-full form-select">
                                <option value="global" {{ old('scope', $msp->scope) == 'global' ? 'selected' : '' }}>Global</option>
                                <option value="country" {{ old('scope', $msp->scope) == 'country' ? 'selected' : '' }}>Country</option>
                                <option value="city" {{ old('scope', $msp->scope) == 'city' ? 'selected' : '' }}>City</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Country</label>
                            <input id="msp-country" type="text" name="country" value="{{ old('country', $msp->country) }}" class="mt-1 block w-full form-input" placeholder="e.g. Lebanon">
                            <p class="text-xs text-gray-500 mt-1">Only for Country or City scope</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input id="msp-city" type="text" name="city" value="{{ old('city', $msp->city) }}" class="mt-1 block w-full form-input" placeholder="e.g. Beirut">
                            <p class="text-xs text-gray-500 mt-1">Only for City scope</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">MSP Amount</label>
                            <input type="number" step="0.01" name="msp_amount" value="{{ old('msp_amount', $msp->msp_amount ?? '') }}" class="mt-1 block w-full form-input" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <input type="text" name="currency" value="{{ old('currency', $msp->currency ?? 'USD') }}" class="mt-1 block w-full form-input" required>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">{{ $mode === 'create' ? 'Create' : 'Update' }}</button>
                        <a href="{{ route('admin.msp.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const scope = document.getElementById('msp-scope');
        const country = document.getElementById('msp-country');
        const city = document.getElementById('msp-city');

        function toggleFields() {
            const val = scope.value;
            if (val === 'global') {
                country.value = '';
                city.value = '';
                country.disabled = true;
                city.disabled = true;
            } else if (val === 'country') {
                country.disabled = false;
                city.value = '';
                city.disabled = true;
            } else { // city
                country.disabled = false;
                city.disabled = false;
            }
        }

        scope.addEventListener('change', toggleFields);
        toggleFields(); // initial
    });
    </script>
    @endpush
</x-app-layout>
