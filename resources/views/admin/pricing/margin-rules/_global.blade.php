{{-- ===========================
    GLOBAL RULE SETTINGS
=========================== --}}
<div class="card mb-4">
    <div class="card-header bg-white border-0">
        <h6 class="mb-1">Global Margin Rules</h6>
        <p class="text-muted small mb-0">
            These rules apply when no country or city rule overrides them.
        </p>
    </div>

    <div class="card-body">
        {{-- GLOBAL BASE MARGIN FIELDS --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Default Margin (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" class="form-control"
                        name="default_margin_percent"
                        value="{{ old('default_margin_percent', $global->default_margin_percent ?? 10) }}">
                    <span class="input-group-text">%</span>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Minimum Margin (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" class="form-control"
                        name="min_margin_percent"
                        value="{{ old('min_margin_percent', $global->min_margin_percent ?? 5) }}">
                    <span class="input-group-text">%</span>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Maximum Margin (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" class="form-control"
                        name="max_margin_percent"
                        value="{{ old('max_margin_percent', $global->max_margin_percent ?? 25) }}">
                    <span class="input-group-text">%</span>
                </div>
            </div>
        </div>

        {{-- ENABLE BASE MARGIN --}}
        @php $enabled = $global->enable_base_margin ?? 1; @endphp
        <div class="mb-4">
            <label class="form-label fw-semibold">Enable Base Margin</label><br>
            <label class="switch">
                <input type="checkbox" name="enable_base_margin" {{ $enabled ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
            <span class="ms-2 small {{ $enabled ? 'text-success' : 'text-danger' }}">
                {{ $enabled ? '🟢 Enabled' : '🔴 Disabled' }}
            </span>
        </div>

        <hr>

        {{-- RULE A --}}
        <h6 class="mb-2">Rule A — Demand Based Margin</h6>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">High demand when rooms ≤</label>
                <input type="number" class="form-control"
                    name="demand_high_threshold_rooms"
                    value="{{ old('demand_high_threshold_rooms', $global->demand_high_threshold_rooms ?? 4) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Increase margin by (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="demand_high_margin_increase_percent"
                    value="{{ old('demand_high_margin_increase_percent', $global->demand_high_margin_increase_percent ?? 5) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Decrease margin by (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="demand_low_margin_decrease_percent"
                    value="{{ old('demand_low_margin_decrease_percent', $global->demand_low_margin_decrease_percent ?? 5) }}">
            </div>
        </div>

        {{-- Toggle --}}
        @php $aEnabled = $global->enable_demand_rule ?? 1; @endphp
        <label class="switch">
            <input type="checkbox" name="enable_demand_rule" {{ $aEnabled ? 'checked' : '' }}>
            <span class="slider round"></span>
        </label>
        <span class="ms-2 small {{ $aEnabled ? 'text-success' : 'text-danger' }}">
            {{ $aEnabled ? '🟢 Enabled' : '🔴 Disabled' }}
        </span>

        <hr>

        {{-- RULE B --}}
        <h6 class="mb-2 mt-4">Rule B — Competitor Pricing</h6>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Price difference threshold (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="competitor_price_diff_threshold_percent"
                    value="{{ old('competitor_price_diff_threshold_percent', $global->competitor_price_diff_threshold_percent ?? 5) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Lower margin by (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="competitor_margin_decrease_percent"
                    value="{{ old('competitor_margin_decrease_percent', $global->competitor_margin_decrease_percent ?? 3) }}">
            </div>
        </div>

        {{-- Toggle --}}
        @php $bEnabled = $global->enable_competitor_rule ?? 1; @endphp
        <label class="switch">
            <input type="checkbox" name="enable_competitor_rule" {{ $bEnabled ? 'checked' : '' }}>
            <span class="slider round"></span>
        </label>
        <span class="ms-2 small {{ $bEnabled ? 'text-success' : 'text-danger' }}">
            {{ $bEnabled ? '🟢 Enabled' : '🔴 Disabled' }}
        </span>

        <hr>

        {{-- RULE C --}}
        <h6 class="mb-2 mt-4">Rule C — Conversion Based</h6>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Low conversion below (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="conversion_threshold_percent"
                    value="{{ old('conversion_threshold_percent', $global->conversion_threshold_percent ?? 1.25) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Decrease margin by (%)</label>
                <input type="number" step="0.01" class="form-control"
                    name="conversion_margin_decrease_percent"
                    value="{{ old('conversion_margin_decrease_percent', $global->conversion_margin_decrease_percent ?? 2) }}">
            </div>
        </div>

        {{-- Toggle --}}
        @php $cEnabled = $global->enable_conversion_rule ?? 1; @endphp
        <label class="switch">
            <input type="checkbox" name="enable_conversion_rule" {{ $cEnabled ? 'checked' : '' }}>
            <span class="slider round"></span>
        </label>
        <span class="ms-2 small {{ $cEnabled ? 'text-success' : 'text-danger' }}">
            {{ $cEnabled ? '🟢 Enabled' : '🔴 Disabled' }}
        </span>

        <div class="text-end mt-4">
            <button class="btn btn-primary">Save Global Rules</button>
        </div>
    </div>
</div>
