<x-app-layout>

    <div class="container-fluid py-4 margin-rules-page">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $g = $global ?? null;
            if (!isset($countries) || !is_array($countries)) {
                $countries = app(\App\Services\HotelbedsService::class)->getStaticCountries();
            }
            if (!isset($destinations) || !is_array($destinations)) {
                $destinations = app(\App\Services\HotelbedsService::class)->getStaticDestinations();
            }
        @endphp

        <div class="card mb-4">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Margins</h6>
                        <p class="text-muted small mb-0">
                            Change margins based on global, country, and city.
                        </p>
                    </div>

                    <ul class="nav nav-pills small" id="marginTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-global"
                                type="button">
                                Global
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-country"
                                type="button">
                                Country
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-city" type="button">
                                City
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- ====================== GLOBAL TAB ====================== --}}
                    <div class="tab-pane fade show active" id="pane-global">
                        <form method="POST" action="{{ route('admin.margin-rules.global.update') }}">
                            @csrf
                            <input type="hidden" name="scope" value="global">

                            <h6 class="card-title mb-3">Default Margin Percentage</h6>
                            <p class="text-muted small mb-4">
                                This is the standard margin applied to all hotels unless a specific rule overrides it.
                            </p>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Default Margin</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            name="default_margin_percent"
                                            value="{{ old('default_margin_percent', $g->default_margin_percent ?? 10) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Minimum Margin</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            name="min_margin_percent"
                                            value="{{ old('min_margin_percent', $g->min_margin_percent ?? 5) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Maximum Margin</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            name="max_margin_percent"
                                            value="{{ old('max_margin_percent', $g->max_margin_percent ?? 25) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Save Global Margins</button>
                            </div>
                        </form>
                    </div>

                    {{-- ====================== COUNTRY TAB ====================== --}}
                    <div class="tab-pane fade" id="pane-country">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Country Margin Rules</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-country">
                                + Add Country Rule
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control js-filter-input"
                                        placeholder="Search for a country" data-target-table="#countryRulesTable">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="countryRulesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Country</th>
                                        <th class="text-center">Margin</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($countryRules as $rule)
                                        <tr data-filter-text="{{ strtolower($rule->country) }}">
                                            <td>{{ $rule->country }}</td>
                                            <td class="text-center">
                                                {{ number_format($rule->default_margin_percent, 2) }}%</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-link btn-sm js-edit-rule"
                                                    data-id="{{ $rule->id }}" data-scope="country">Edit</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm text-danger js-delete-rule"
                                                    data-url="{{ route('admin.margin-rules.destroy', $rule->id) }}">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No country rules added
                                                yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ====================== CITY TAB ====================== --}}
                    <div class="tab-pane fade" id="pane-city">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">City Margin Rules</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-city">
                                + Add City Rule
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control js-filter-input"
                                        placeholder="Search for a city/country" data-target-table="#cityRulesTable">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="cityRulesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>City</th>
                                        <th>Country</th>
                                        <th class="text-center">Margin</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cityRules as $rule)
                                        <tr data-filter-text="{{ strtolower($rule->city . ' ' . $rule->country) }}">
                                            <td>{{ $rule->city }}</td>
                                            <td>{{ $rule->country }}</td>
                                            <td class="text-center">
                                                {{ number_format($rule->default_margin_percent, 2) }}%</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-link btn-sm js-edit-rule"
                                                    data-id="{{ $rule->id }}" data-scope="city">Edit</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm text-danger js-delete-rule"
                                                    data-url="{{ route('admin.margin-rules.destroy', $rule->id) }}">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No city rules added yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================== PARAMETERS FORM ====================== --}}
        <form method="POST" action="{{ route('admin.margin-rules.parameters.update') }}">
            @csrf
            <div class="card mb-4">
                <div class="card-body">

                    <div class="row">
                        {{-- RULE A --}}
                        <div class="col-lg-6 mb-3">
                            <div class="border p-3 rounded h-100">

                                <h6 class="fw-bold mb-0">RULE A – Demand Based</h6><br>
                                <p class="text-grey">
                                    Detects high demand based on low availability
                                    If demand is high → system increases margin
                                    This is always an increase, never a decrease
                                    The logic (low room availability = high demand) is fixed
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Enable/Disable</h6><br>

                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="enable_demand_rule" value="1"
                                            class="form-check-input" @checked($parameters->enable_demand_rule ?? true)>
                                    </div>
                                </div>

                                <label class="small fw-semibold mt-2">Availability Threshold (rooms)</label>
                                <p class="text-grey">Number of rooms to be considered as high demand?</p>
                                <input type="number" name="demand_high_threshold_rooms" class="form-control mb-2"
                                    value="{{ $parameters->demand_high_threshold_rooms ?? 4 }}">

                                <label class="small fw-semibold">Increase Margin by (%)</label>
                                <input type="number" step="0.01" name="demand_high_margin_increase_percent"
                                    class="form-control mb-2"
                                    value="{{ $parameters->demand_high_margin_increase_percent ?? 5 }}">

                                <label class="small fw-semibold">Decrease Margin by (%)</label>
                                <input type="number" step="0.01" name="demand_low_margin_decrease_percent"
                                    class="form-control"
                                    value="{{ $parameters->demand_low_margin_decrease_percent ?? 5 }}">
                            </div>
                        </div>

                        {{-- RULE B --}}
                        <div class="col-lg-6 mb-3">
                            <div class="border p-3 rounded h-100">
                                <h6 class="fw-bold mb-0">RULE B – Competitor Price</h6>
                                <p class="text-grey">
                                    Compares your selling price to competitor prices (from parity API or scraped
                                    sources)
                                    If your price is higher than competitors, the system reduces your margin
                                    Purpose: keep you competitively priced
                                    Rule always reduces the margin, never increases
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Enable/Disable</h6><br>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="enable_competitor_rule" value="1"
                                            class="form-check-input" @checked($parameters->enable_competitor_rule ?? true)>
                                    </div>
                                </div>

                                <label class="small fw-semibold mt-2">Price Difference Threshold (%)</label>
                                <p class="text-grey">How much more expensive can we be before lowering margin?</p>
                                <input type="number" step="0.01" name="competitor_price_diff_threshold_percent"
                                    class="form-control mb-2"
                                    value="{{ $parameters->competitor_price_diff_threshold_percent ?? 5 }}">

                                <label class="small fw-semibold">Margin Decrease Amount (%)</label>
                                <p class="text-grey">How much margin should we reduce when we are more expensive?</p>
                                <input type="number" step="0.01" name="competitor_margin_decrease_percent"
                                    class="form-control"
                                    value="{{ $parameters->competitor_margin_decrease_percent ?? 3 }}">
                            </div>
                        </div>

                        {{-- RULE C --}}
                        <div class="col-lg-6 mb-3">
                            <div class="border p-3 rounded h-100">
                                <h6 class="fw-bold mb-0">RULE C – Conversion Rate</h6>
                                <p class="text-grey">Checks a hotel’s conversion performance If the hotel is
                                    underperforming, system reduces the margin This helps the hotel become more
                                    competitive The rule always reduces margins, never increases</p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Enable/Disable</h6><br>

                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="enable_conversion_rule" value="1"
                                            class="form-check-input" @checked($parameters->enable_conversion_rule ?? true)>
                                    </div>
                                </div>

                                <label class="small fw-semibold mt-2">Conversion Threshold (%)</label>
                                <p class="text-grey">What conversion rate is considered low performance?</p>
                                <input type="number" step="0.01" name="conversion_threshold_percent"
                                    class="form-control mb-2"
                                    value="{{ $parameters->conversion_threshold_percent ?? 1.25 }}">

                                <label class="small fw-semibold">Decrease Margin by (%)</label>
                                <input type="number" step="0.01" name="conversion_margin_decrease_percent"
                                    class="form-control"
                                    value="{{ $parameters->conversion_margin_decrease_percent ?? 2 }}">
                            </div>
                        </div>

                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Save Rule Parameters</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ====================== COUNTRY MODAL ====================== --}}
        <div class="modal fade" id="countryRuleModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="countryRuleForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="countryFormMethod" value="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="countryRuleTitle">Add Country Rule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="fw-semibold small">Select Country</label>
                                <select name="country" class="form-select" id="countryInput" required>
                                    <option value="" disabled selected>-- Select Country --</option>
                                    @foreach ($countries as $c)
                                        @php
                                            $code = $c['code'] ?? $c->code;
                                            $name = $c['name'] ?? $c->name;
                                        @endphp
                                        <option value="{{ $code }}">{{ $name }}
                                            ({{ $code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="fw-semibold small">Margin (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control"
                                        id="countryMarginInput" name="default_margin_percent" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Country Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ====================== CITY MODAL ====================== --}}
        <div class="modal fade" id="cityRuleModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="cityRuleForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="cityFormMethod" value="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cityRuleTitle">Add City Rule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 position-relative">
                                <label class="fw-semibold small">Search City</label>
                                <input type="text" id="cityInput" class="form-control"
                                    placeholder="Search by city/country" autocomplete="off">
                                <input type="hidden" id="cityCodeInput" name="city">
                                <div id="cityDropdown" class="list-group position-absolute w-100 shadow-sm"
                                    style="z-index: 9999; display:none; max-height:200px; overflow-y:auto"></div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-semibold small">Country</label>
                                <select name="country" class="form-select" id="cityCountryInput" required>
                                    <option value="">-- Select Country --</option>
                                    @foreach ($countries as $c)
                                        @php
                                            $code = $c['code'] ?? $c->code;
                                            $name = $c['name'] ?? $c->name;
                                        @endphp
                                        <option value="{{ $code }}">{{ $name }}
                                            ({{ $code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="fw-semibold small">Margin (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="cityMarginInput"
                                        name="default_margin_percent" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save City Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // const jsonUrlBase = "{{ url('admin/pricing/margin-rules') }}";
            const jsonUrlBase = "{{ url('admin/pricing/margin-rules') }}";

            const globalDefault = {{ (float) ($global->default_margin_percent ?? 10) }};

            // Filter Logic
            document.querySelectorAll('.js-filter-input').forEach(input => {
                input.addEventListener('keyup', function() {
                    const term = this.value.toLowerCase();
                    const table = document.querySelector(this.dataset.targetTable);
                    table.querySelectorAll('tbody tr').forEach(tr => {
                        tr.style.display = tr.dataset.filterText.includes(term) ? '' :
                            'none';
                    });
                });
            });

            // Delete Logic
            document.querySelectorAll('.js-delete-rule').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm("Delete rule?")) return;
                    const url = this.dataset.url;
                    const fd = new FormData();
                    fd.append('_token', "{{ csrf_token() }}");
                    fd.append('_method', "DELETE");
                    fetch(url, {
                        method: "POST",
                        body: fd
                    }).then(() => location.reload());
                });
            });

            // Country Modal
            const countryModal = new bootstrap.Modal(document.getElementById('countryRuleModal'));
            document.getElementById('btn-add-country').addEventListener('click', () => {
                document.getElementById('countryRuleForm').reset();
                document.getElementById('countryFormMethod').value = "POST";
                document.getElementById('countryRuleForm').action =
                    "{{ route('admin.margin-rules.country.store') }}";
                document.getElementById('countryMarginInput').value = globalDefault;
                document.getElementById('countryRuleTitle').innerText = "Add Country Rule";
                countryModal.show();
            });

            document.querySelectorAll('[data-scope="country"].js-edit-rule').forEach(btn => {
                btn.addEventListener('click', function() {

                    const id = this.dataset.id;
                    const form = document.getElementById('countryRuleForm');

                    form.reset(); // 🔥 IMPORTANT

                    fetch(jsonUrlBase + "/" + id + "/json")
                        .then(r => r.json())
                        .then(data => {

                            form.action = "{{ url('admin/pricing/margin-rules/country') }}/" +
                                id;
                            document.getElementById('countryFormMethod').value = "PUT";

                            // ✅ force select
                            const select = document.getElementById('countryInput');
                            select.value = data.country;

                            document.getElementById('countryMarginInput').value =
                                parseFloat(data.default_margin_percent).toFixed(2);

                            document.getElementById('countryRuleTitle').innerText =
                                "Edit Country Rule";
                            countryModal.show();
                        });
                });
            });


            // City Modal & Autocomplete
            const cityModal = new bootstrap.Modal(document.getElementById('cityRuleModal'));
            window.__DESTS__ = {!! json_encode(array_values($destinations)) !!};

            document.getElementById('btn-add-city').addEventListener('click', () => {
                document.getElementById('cityRuleForm').reset();
                document.getElementById('cityFormMethod').value = "POST";
                document.getElementById('cityRuleForm').action =
                    "{{ route('admin.margin-rules.city.store') }}";
                document.getElementById('cityMarginInput').value = globalDefault;
                document.getElementById('cityRuleTitle').innerText = "Add City Rule";
                cityModal.show();
            });

            document.querySelectorAll('[data-scope="city"].js-edit-rule').forEach(btn => {
                btn.addEventListener('click', function() {

                    const id = this.dataset.id;
                    const form = document.getElementById('cityRuleForm');

                    form.reset(); // 🔥 IMPORTANT

                    fetch(jsonUrlBase + "/" + id + "/json")
                        .then(r => r.json())
                        .then(data => {

                            form.action = "{{ url('admin/pricing/margin-rules/city') }}/" + id;
                            document.getElementById('cityFormMethod').value = "PUT";

                            // Find city name from destinations using code
                            const dest = window.__DESTS__.find(d => d.code === data.city);

                            document.getElementById('cityCodeInput').value = data.city;
                            document.getElementById('cityInput').value =
                                dest ? `${dest.name} (${dest.code})` : data.city;

                            document.getElementById('cityCountryInput').value = data.country;
                            document.getElementById('cityMarginInput').value =
                                parseFloat(data.default_margin_percent).toFixed(2);

                            document.getElementById('cityRuleTitle').innerText =
                                "Edit City Rule";
                            cityModal.show();
                        });
                });
            });


            // City Search
            const cityInput = document.getElementById('cityInput');
            const cityDropdown = document.getElementById('cityDropdown');
            cityInput.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                if (!q) {
                    cityDropdown.style.display = "none";
                    return;
                }
                const matches = window.__DESTS__.filter(d => d.name.toLowerCase().includes(q)).slice(0, 10);
                cityDropdown.innerHTML = "";
                matches.forEach(d => {
                    const btn = document.createElement('button');
                    btn.type = "button";
                    btn.className = "list-group-item list-group-item-action";
                    btn.innerText = `${d.name} (${d.code})`;
                    btn.onclick = () => {
                        cityInput.value = d.name;
                        document.getElementById('cityCodeInput').value = d.code;
                        cityDropdown.style.display = "none";
                    };
                    cityDropdown.appendChild(btn);
                });
                cityDropdown.style.display = matches.length ? "block" : "none";
            });
        });
    </script>
</x-app-layout>
