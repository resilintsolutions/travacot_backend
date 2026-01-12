<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $mode === 'create' ? 'Add Margin Rule' : 'Edit Margin Rule' }}
                </h2>
                <p class="text-sm text-gray-500">{{ $mode === 'create' ? 'Create a new margin rule' : 'Edit the margin rule' }}</p>
            </div>

            <div>
                <a href="{{ route('admin.margin-rules.index') }}" class="btn btn-secondary">Back to list</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ $mode === 'create' ? route('admin.margin-rules.store') : route('admin.margin-rules.update', $rule) }}">
                    @csrf
                    @if($mode === 'edit') @method('PUT') @endif

                    {{-- Scope --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scope</label>
                            <select id="scope" name="scope" class="mt-1 block w-full form-select">
                                <option value="global" {{ old('scope', $rule->scope) == 'global' ? 'selected' : '' }}>Global</option>
                                <option value="country" {{ old('scope', $rule->scope) == 'country' ? 'selected' : '' }}>Country</option>
                                <option value="city" {{ old('scope', $rule->scope) == 'city' ? 'selected' : '' }}>City</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Country</label>
                            <input id="country" type="text" name="country" value="{{ old('country', $rule->country) }}" class="mt-1 block w-full form-input" placeholder="e.g. Lebanon">
                            <p class="text-xs text-gray-500 mt-1">Only for Country or City scope</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input id="city" type="text" name="city" value="{{ old('city', $rule->city) }}" class="mt-1 block w-full form-input" placeholder="e.g. Beirut">
                            <p class="text-xs text-gray-500 mt-1">Only for City scope</p>
                        </div>
                    </div>

                    {{-- Top block: default/min/max --}}
                    <div class="mb-4 border rounded p-4">
                        <h3 class="font-medium mb-3">Top-level margins</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Default Margin (%)</label>
                                <input type="number" step="0.01" name="default_margin_percent" value="{{ old('default_margin_percent', $rule->default_margin_percent ?? 10.00) }}" class="mt-1 block w-full form-input" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum Margin Allowed (%)</label>
                                <input type="number" step="0.01" name="min_margin_percent" value="{{ old('min_margin_percent', $rule->min_margin_percent ?? 5.00) }}" class="mt-1 block w-full form-input" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Maximum Margin Allowed (%)</label>
                                <input type="number" step="0.01" name="max_margin_percent" value="{{ old('max_margin_percent', $rule->max_margin_percent ?? 25.00) }}" class="mt-1 block w-full form-input" required>
                            </div>
                        </div>
                    </div>

                    {{-- RULE A – Demand Based --}}
                    <div class="mb-4 border rounded p-4">
                        <h3 class="font-medium mb-2">Rule A — Demand-based margin (increases only)</h3>
                        <p class="text-sm text-gray-500 mb-3">If short availability is below threshold, increase margin by the configured percent. This rule only increases margin.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Availability Threshold (rooms)</label>
                                <input type="number" step="1" name="demand_high_threshold_rooms" value="{{ old('demand_high_threshold_rooms', $rule->demand_high_threshold_rooms ?? 4) }}" class="mt-1 block w-full form-input" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">High-demand margin increase (%)</label>
                                <input type="number" step="0.01" name="demand_high_margin_increase_percent" value="{{ old('demand_high_margin_increase_percent', $rule->demand_high_margin_increase_percent ?? 5.00) }}" class="mt-1 block w-full form-input" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Low-demand margin decrease (%)</label>
                                <input type="number" step="0.01" name="demand_low_margin_decrease_percent" value="{{ old('demand_low_margin_decrease_percent', $rule->demand_low_margin_decrease_percent ?? 5.00) }}" class="mt-1 block w-full form-input" required>
                                <p class="text-xs text-gray-500 mt-1">Used to optionally reduce margin on low demand (kept positive; logic will subtract it).</p>
                            </div>
                        </div>
                    </div>

                    {{-- RULE B --}}
                    <div class="mb-4 border rounded p-4">
                        <h3 class="font-medium mb-2">Rule B — Competitor Price Rule</h3>
                        <p class="text-sm text-gray-500 mb-3">If our market price is significantly higher than competitor (threshold), reduce margin by the configured percent.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price Difference Threshold (%)</label>
                                <input type="number" step="0.01" name="competitor_price_diff_threshold_percent" value="{{ old('competitor_price_diff_threshold_percent', $rule->competitor_price_diff_threshold_percent ?? 5.00) }}" class="mt-1 block w-full form-input" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Margin Decrease Amount (%)</label>
                                <input type="number" step="0.01" name="competitor_margin_decrease_percent" value="{{ old('competitor_margin_decrease_percent', $rule->competitor_margin_decrease_percent ?? -3.00) }}" class="mt-1 block w-full form-input" required>
                                <p class="text-xs text-gray-500 mt-1">Negative values reduce margin; positive values increase it.</p>
                            </div>
                        </div>
                    </div>

                    {{-- RULE C --}}
                    <div class="mb-4 border rounded p-4">
                        <h3 class="font-medium mb-2">Rule C — Conversion-based margin</h3>
                        <p class="text-sm text-gray-500 mb-3">If a hotel's conversion rate (sales/visits) is below the threshold, reduce margin by the configured percent.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Conversion Threshold (%)</label>
                                <input type="number" step="0.01" name="conversion_threshold_percent" value="{{ old('conversion_threshold_percent', $rule->conversion_threshold_percent ?? 1.20) }}" class="mt-1 block w-full form-input" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Conversion Margin Decrease (%)</label>
                                <input type="number" step="0.01" name="conversion_margin_decrease_percent" value="{{ old('conversion_margin_decrease_percent', $rule->conversion_margin_decrease_percent ?? -2.00) }}" class="mt-1 block w-full form-input" required>
                                <p class="text-xs text-gray-500 mt-1">Negative to reduce; positive to increase (rare).</p>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">{{ $mode === 'create' ? 'Create Rule' : 'Update Rule' }}</button>
                        <a href="{{ route('admin.margin-rules.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- JS to toggle fields based on scope --}}
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const scope = document.getElementById('scope');
        const country = document.getElementById('country');
        const city = document.getElementById('city');

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
