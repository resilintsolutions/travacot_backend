<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl">Promo Engine</h2>
                <p class="text-muted small mb-0">Control Ongoing Deals and validate discount safety.</p>
            </div>
        </div>
    </x-slot>

    <style>
        .promo-card {
            border: 1px solid #e3e8f2;
            border-radius: 14px;
            background: #f7f9fd;
        }
        .promo-card h6 {
            font-weight: 600;
        }
        .promo-badge {
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
        }
        .promo-badge.light { background: #e8f8f2; color: #0f6b49; border: 1px solid #bfe8d6; }
        .promo-badge.normal { background: #edf0ff; color: #3437ad; border: 1px solid #c9d1ff; }
        .promo-badge.aggressive { background: #fff7e8; color: #9a5b00; border: 1px solid #ffe0b3; }
        .promo-toggle {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 54px;
            height: 28px;
        }
        .switch input { display: none; }
        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            transition: 0.2s;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: 0.2s;
        }
        .switch input:checked + .slider {
            background: #111827;
        }
        .switch input:checked + .slider:before {
            transform: translateX(26px);
        }
        .promo-input {
            width: 72px;
            border-radius: 10px;
            border: 1px solid #d7dfec;
            padding: 6px 8px;
            text-align: center;
            background: #fff;
        }
        .promo-muted {
            color: #6b7280;
            font-size: 12px;
        }
    </style>

    <div class="py-6">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.promo-engine.update') }}">
                @csrf
                @method('PUT')

                <div class="promo-card p-4 mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Promo Engine Status</h6>
                        <div class="promo-muted">Turn the global promo engine on or off. When off, no promo cards are shown on any hotel.</div>
                    </div>
                    <div class="promo-toggle">
                        <span class="text-uppercase promo-muted">Engine</span>
                        <label class="switch">
                            <input type="hidden" name="engine_status" value="0">
                            <input type="checkbox" name="engine_status" value="1" {{ $settings->engine_status ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <h6 class="mb-3">Promo Modes</h6>
                @php $modes = $settings->enabled_modes ?? []; @endphp
                <div class="row g-3 mb-4">
                    <div class="col-lg-4">
                        <div class="promo-card p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong>Light</strong>
                                    <div class="promo-muted">Safe, small discounts for conservative promos</div>
                                </div>
                                <span class="promo-badge light">Low Impact</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="promo-muted">Range</span>
                                <input class="promo-input" value="10" disabled>
                                <span class="promo-muted">% to</span>
                                <input class="promo-input" value="20" disabled>
                                <span class="promo-muted">% of margin</span>
                            </div>
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <input type="checkbox" name="enabled_modes[]" value="light" {{ in_array('light', $modes) ? 'checked' : '' }}>
                                <span class="promo-muted">Enable</span>
                            </div>
                            <div class="promo-muted mt-2">E.g. if a hotel margin is 12%, Light mode can use 1.2%–2.4% as discount</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="promo-card p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong>Normal</strong>
                                    <div class="promo-muted">Standard discounts for most inventory</div>
                                </div>
                                <span class="promo-badge normal">Balanced</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="promo-muted">Range</span>
                                <input class="promo-input" value="21" disabled>
                                <span class="promo-muted">% to</span>
                                <input class="promo-input" value="40" disabled>
                                <span class="promo-muted">% of margin</span>
                            </div>
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <input type="checkbox" name="enabled_modes[]" value="normal" {{ in_array('normal', $modes) ? 'checked' : '' }}>
                                <span class="promo-muted">Enable</span>
                            </div>
                            <div class="promo-muted mt-2">E.g. if a hotel margin is 12%, Normal mode can use 2.4%–4.8% as discount</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="promo-card p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong>Aggressive</strong>
                                    <div class="promo-muted">Stronger discounts for competitive markets</div>
                                </div>
                                <span class="promo-badge aggressive">High Impact</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="promo-muted">Range</span>
                                <input class="promo-input" value="41" disabled>
                                <span class="promo-muted">% to</span>
                                <input class="promo-input" value="70" disabled>
                                <span class="promo-muted">% of margin</span>
                            </div>
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <input type="checkbox" name="enabled_modes[]" value="aggressive" {{ in_array('aggressive', $modes) ? 'checked' : '' }}>
                                <span class="promo-muted">Enable</span>
                            </div>
                            <div class="promo-muted mt-2">E.g. if a hotel margin is 12%, Aggressive mode can use 4.8%–8.4% as discount</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="promo-card p-4 h-100">
                            <h6 class="mb-1">Safety Rules / Buffer Zone</h6>
                            <div class="promo-muted mb-3">Global protections to make sure promos never destroy your margin. These rules are applied before any destination or property overrides.</div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="promo-muted">Minimum Margin Required</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <input class="promo-input" type="number" step="0.01" name="min_margin_eligibility" value="{{ $settings->min_margin_eligibility }}">
                                        <span class="promo-muted">%</span>
                                    </div>
                                    <div class="promo-muted mt-2">Hotels below this never receive promos</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="promo-muted">Safety Buffer</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <input class="promo-input" type="number" step="0.01" name="safety_buffer" value="{{ $settings->safety_buffer }}">
                                        <span class="promo-muted">%</span>
                                    </div>
                                    <div class="promo-muted mt-2">Final margin after discount must be at least this</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="promo-muted">Minimum Profit After Promo</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <input class="promo-input" type="number" step="0.01" name="min_profit_after_promo" value="{{ $settings->min_profit_after_promo }}">
                                        <span class="promo-muted">%</span>
                                    </div>
                                    <div class="promo-muted mt-2">Hard floor for final profit per booking</div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-2 promo-muted">
                                    <input type="hidden" name="hide_promo_on_fail" value="0">
                                    <input type="checkbox" name="hide_promo_on_fail" value="1" {{ $settings->hide_promo_on_fail ? 'checked' : '' }}>
                                    Hide promo card if a hotel fails any safety rule.
                                </label>
                                <label class="d-flex align-items-center gap-2 promo-muted">
                                    <input type="hidden" name="auto_downgrade_enabled" value="0">
                                    <input type="checkbox" name="auto_downgrade_enabled" value="1" {{ $settings->auto_downgrade_enabled ? 'checked' : '' }}>
                                    Auto‑downgrade from Aggressive to Normal if Aggressive fails checks.
                                </label>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <div class="promo-muted">Discount Strategy</div>
                                    <select class="form-select mt-1" name="discount_strategy">
                                        <option value="max_safe" {{ $settings->discount_strategy === 'max_safe' ? 'selected' : '' }}>Max Safe</option>
                                        <option value="midpoint" {{ $settings->discount_strategy === 'midpoint' ? 'selected' : '' }}>Midpoint</option>
                                        <option value="min" {{ $settings->discount_strategy === 'min' ? 'selected' : '' }}>Minimum</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="promo-muted">Attribution Window (minutes)</div>
                                    <input class="form-control mt-1" type="number" min="1" name="attribution_window_minutes" value="{{ $settings->attribution_window_minutes }}">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="promo-card p-4 h-100">
                            <h6 class="mb-1">Safety Example</h6>
                            <div class="promo-muted mb-3">This is how the engine validates a hotel before showing a promo</div>

                            <div class="d-flex justify-content-between promo-muted mb-2">
                                <span>Minimum Margin Required</span>
                                <strong>{{ $example['margin'] }}%</strong>
                            </div>
                            <div class="d-flex justify-content-between promo-muted mb-2">
                                <span>Mode</span>
                                <strong>{{ $example['mode'] }} (40‑70% of margin)</strong>
                            </div>
                            <div class="d-flex justify-content-between promo-muted mb-2">
                                <span>Requested discount</span>
                                <strong>{{ $example['discount'] }}%</strong>
                            </div>
                            <div class="d-flex justify-content-between promo-muted mb-2">
                                <span>Final Margin</span>
                                <strong>{{ $example['final_margin'] }}%</strong>
                            </div>

                            <div class="mt-3">
                                <span class="promo-muted">Result</span>
                                <div class="mt-2">
                                    @if($example['blocked'])
                                        <span class="text-danger fw-semibold">Blocked</span>
                                        <span class="promo-muted"> – Below {{ $example['min_required'] }}% minimum profit.</span>
                                    @else
                                        <span class="text-success fw-semibold">Approved</span>
                                        <span class="promo-muted"> – Above {{ $example['min_required'] }}% minimum profit.</span>
                                    @endif
                                </div>
                            </div>

                            <div class="promo-muted mt-3">
                                If “auto‑downgrade” is enabled, the system will retry using Normal mode before giving up on this hotel.
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
