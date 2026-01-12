{{-- ============================================================
   COUNTRY RULE MODAL
   ============================================================ --}}
<div class="modal fade" id="countryRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="countryRuleForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="countryFormMethod" value="POST">
                <input type="hidden" name="scope" value="country">

                <div class="modal-header">
                    <h5 class="modal-title" id="countryRuleTitle">Add Country Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted small">
                        A country rule overrides the global margin, but a city rule will override this.
                    </p>

                    {{-- Country select --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Country</label>

                        <select class="form-select form-select-sm" name="country" id="countryInput">
                            <option value="" disabled selected>-- Select a Country --</option>

                            @foreach ($countries as $c)
                                @php
                                    $code = $c['code'] ?? $c->code;
                                    $name = $c['name'] ?? $c->name;
                                @endphp
                                <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Margin input --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Margin (%)</label>

                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" class="form-control"
                                   id="countryMarginInput"
                                   name="default_margin_percent">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    {{-- Enabled switch --}}
                    <div class="mb-3 mt-2">
                        <label class="form-label small fw-semibold">Enable Country Rule</label>
                        <br>
                        <label class="switch">
                            <input type="checkbox" name="is_enabled" id="countryEnabledInput" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    {{-- Button to load global margin --}}
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCountryUseGlobal">
                        Use Global Margin ({{ $global->default_margin_percent ?? 0 }}%)
                    </button>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="countrySubmitBtn">
                        Save Country Rule
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>





{{-- ============================================================
   CITY RULE MODAL  (Destination-based)
   ============================================================ --}}
<div class="modal fade" id="cityRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="cityRuleForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="cityFormMethod" value="POST">
                <input type="hidden" name="scope" value="city">

                <div class="modal-header">
                    <h5 class="modal-title" id="cityRuleTitle">Add City Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted small">
                        A city rule (destination-based) overrides both global & country rules.
                    </p>

                    {{-- City autocomplete --}}
                    <div class="mb-3 position-relative">
                        <label class="form-label small fw-semibold">Search City</label>

                        {{-- Visible input for user --}}
                        <input type="text"
                               class="form-control form-control-sm"
                               id="cityInput"
                               placeholder="Search by name or destination code..."
                               autocomplete="off">

                        {{-- Hidden field submitted to backend --}}
                        <input type="hidden" name="city" id="cityCodeInput">

                        {{-- Dropdown results --}}
                        <div id="cityDropdown"
                             class="list-group position-absolute w-100 shadow-sm"
                             style="z-index:9999; display:none; max-height:260px; overflow-y:auto;"></div>
                    </div>

                    {{-- Country (auto prefilled when city selected) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Country</label>

                        <select class="form-select form-select-sm" name="country" id="cityCountryInput">
                            <option value="">-- Select Country --</option>
                            @foreach ($countries as $c)
                                @php $cc = $c['code'] ?? $c->code; $name = $c['name'] ?? $c->name; @endphp
                                <option value="{{ $cc }}">{{ $name }} ({{ $cc }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Margin input --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Margin (%)</label>

                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" class="form-control"
                                   id="cityMarginInput"
                                   name="default_margin_percent">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    {{-- Enabled switch --}}
                    <div class="mb-3 mt-2">
                        <label class="form-label small fw-semibold">Enable City Rule</label>
                        <br>
                        <label class="switch">
                            <input type="checkbox" name="is_enabled" id="cityEnabledInput" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    {{-- Button: load default global margin --}}
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCityUseDefault">
                        Use Global Margin ({{ $global->default_margin_percent ?? 0 }}%)
                    </button>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="citySubmitBtn">
                        Save City Rule
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
