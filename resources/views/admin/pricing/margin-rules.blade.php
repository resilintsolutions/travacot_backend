<x-app-layout>

@section('content')

    <div class="mb-3 text-xs text-gray-500">
        Pricing &amp; Revenue <span class="mx-1">›</span> Margin Rules
    </div>

<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="card mb-3">
        <div class="card-body">
            
        <div>
            <div class="inv-header-title">Margin Rules</div>
            <div class="inv-page-title">Set margin rules (profit rules).</div>
        </div>
            {{-- Header and tabs --}}
            <div class="mb-3 border-bottom pb-2">
                <h6 class="mb-1 fw-semibold">Margins</h6>
                <small class="text-muted">
                    Change margins based on global, city, and hotel.
                </small>

                <ul class="nav nav-tabs mt-3">
                    <li class="nav-item">
                        <span class="nav-link active">Global</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link disabled">Country</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link disabled">City</span>
                    </li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.margin-rules.global.update') }}">
                @csrf

                {{-- Top block: default/min/max margin --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <h6 class="fw-semibold">Default Margin Percentage</h6>
                        <p class="text-muted small">
                            This is the standard margin applied to all hotels unless a specific rule overrides it.
                        </p>
                        <div class="d-inline-flex align-items-center border rounded-3 px-3 py-2 bg-light">
                            <input type="number"
                                   step="0.01"
                                   name="default_margin_percent"
                                   value="{{ old('default_margin_percent', $global->default_margin_percent) }}"
                                   class="form-control border-0 p-0 shadow-none text-center"
                                   style="width: 70px;">
                            <span class="ms-1">%</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="fw-semibold">Minimum Margin Allowed</h6>
                        <p class="text-muted small">
                            This is the lowest margin the system will ever apply, even if other rules suggest a smaller margin.
                        </p>
                        <div class="d-inline-flex align-items-center border rounded-3 px-3 py-2 bg-light">
                            <input type="number"
                                   step="0.01"
                                   name="min_margin_percent"
                                   value="{{ old('min_margin_percent', $global->min_margin_percent) }}"
                                   class="form-control border-0 p-0 shadow-none text-center"
                                   style="width: 70px;">
                            <span class="ms-1">%</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="fw-semibold">Maximum Margin Allowed</h6>
                        <p class="text-muted small">
                            This is the highest margin the system will apply, even if other rules suggest a higher margin.
                        </p>
                        <div class="d-inline-flex align-items-center border rounded-3 px-3 py-2 bg-light">
                            <input type="number"
                                   step="0.01"
                                   name="max_margin_percent"
                                   value="{{ old('max_margin_percent', $global->max_margin_percent) }}"
                                   class="form-control border-0 p-0 shadow-none text-center"
                                   style="width: 70px;">
                            <span class="ms-1">%</span>
                        </div>
                    </div>
                </div>

                {{-- RULE A – Demand based margin --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-semibold mb-1">RULE A – Demand Based Margin</h6>
                            <p class="text-muted small mb-3">
                                Detects high demand based on low availability. If demand is high, the system increases margin.
                                The rule only increases margin, never decreases.
                            </p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1">
                                    Availability Threshold
                                </label>
                                <div class="text-muted small mb-1">
                                    Number of rooms to be considered as high demand?
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small">High demand when available rooms ≤</span>
                                    <input type="number"
                                           min="1"
                                           name="demand_high_threshold_rooms"
                                           value="{{ old('demand_high_threshold_rooms', $global->demand_high_threshold_rooms) }}"
                                           class="form-control form-control-sm"
                                           style="width: 90px;">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    If high demand is detected:
                                </div>
                                <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                    <span class="me-1 small">Margin increases by</span>
                                    <input type="number"
                                           step="0.01"
                                           name="demand_high_margin_increase_percent"
                                           value="{{ old('demand_high_margin_increase_percent', $global->demand_high_margin_increase_percent) }}"
                                           class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                           style="width: 60px;">
                                    <span class="ms-1">%</span>
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    If demand is low:
                                </div>
                                <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                    <span class="me-1 small">Margin decreases by</span>
                                    <input type="number"
                                           step="0.01"
                                           name="demand_low_margin_decrease_percent"
                                           value="{{ old('demand_low_margin_decrease_percent', $global->demand_low_margin_decrease_percent) }}"
                                           class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                           style="width: 60px;">
                                    <span class="ms-1">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RULE B – Competitor price rule --}}
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-semibold mb-1">RULE B – Competitor Price Rule</h6>
                            <p class="text-muted small mb-3">
                                Compares your selling price to competitor prices. If you are too expensive, the system reduces your margin.
                                This rule always reduces margin, never increases.
                            </p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1">
                                    Price Difference Threshold
                                </label>
                                <div class="text-muted small mb-1">
                                    How much more expensive can we be before lowering margin?
                                </div>
                                <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                    <input type="number"
                                           step="0.01"
                                           name="competitor_price_diff_threshold_percent"
                                           value="{{ old('competitor_price_diff_threshold_percent', $global->competitor_price_diff_threshold_percent) }}"
                                           class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                           style="width: 70px;">
                                    <span class="ms-1">%</span>
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold small mb-1">
                                    Margin Decrease Amount
                                </label>
                                <div class="text-muted small mb-1">
                                    How much margin should we reduce when we are more expensive?
                                </div>
                                <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                    <input type="number"
                                           step="0.01"
                                           name="competitor_margin_decrease_percent"
                                           value="{{ old('competitor_margin_decrease_percent', $global->competitor_margin_decrease_percent) }}"
                                           class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                           style="width: 70px;">
                                    <span class="ms-1">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RULE C – Conversion based margin --}}
                <div class="border rounded-3 p-3 mb-3">
                    <h6 class="fw-semibold mb-1">RULE C – Conversion-Based Margin</h6>
                    <p class="text-muted small mb-3">
                        Checks a hotel’s conversion performance. If a hotel is underperforming, the system reduces margin
                        to make it more competitive. This rule always reduces margin, never increases.
                    </p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">
                                Conversion Threshold
                            </label>
                            <div class="text-muted small mb-1">
                                What conversion rate is considered low performance?
                            </div>
                            <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                <input type="number"
                                       step="0.01"
                                       name="conversion_threshold_percent"
                                       value="{{ old('conversion_threshold_percent', $global->conversion_threshold_percent) }}"
                                       class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                       style="width: 70px;">
                                <span class="ms-1">%</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">
                                Lower the margin by
                            </label>
                            <div class="text-muted small mb-1">
                                Margin reduction to apply if conversion is below threshold.
                            </div>
                            <div class="d-inline-flex align-items-center border rounded-pill px-3 py-1">
                                <input type="number"
                                       step="0.01"
                                       name="conversion_margin_decrease_percent"
                                       value="{{ old('conversion_margin_decrease_percent', $global->conversion_margin_decrease_percent) }}"
                                       class="form-control form-control-sm border-0 p-0 shadow-none text-center"
                                       style="width: 70px;">
                                <span class="ms-1">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    Save Margin Rules
                </button>
            </form>

        </div>
    </div>
</div>
</x-app-layout>
