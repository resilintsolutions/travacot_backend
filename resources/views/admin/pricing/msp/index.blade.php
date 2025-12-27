{{-- resources/views/admin/pricing/msp/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl">MSP (Minimum Selling Price)</h2>
                <p class="text-muted small mb-0">Manage global / country / city MSP rules. City overrides Country which overrides Global.</p>
            </div>
            <div>
                <button class="btn btn-primary btn-sm" id="btn-add-msp">Add MSP Rule</button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            {{-- flash messages --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#msp-global">Global</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#msp-country">Country</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#msp-city">City</a></li>
                    </ul>

                    <div class="tab-content">
                        {{-- GLOBAL --}}
                        <div class="tab-pane show active" id="msp-global">
                            <div class="small text-muted">The global MSP will be used when no country or city override exists</div>
                            <div class="mt-3 d-flex align-items-center">
                                @if($global)
                                    <div class="h5 mb-0">${{ number_format($global->msp_amount,2) }} <small class="text-muted ms-2">{{ $global->currency }}</small></div>
                                    <a href="{{ route('admin.msp.edit', $global) }}" class="btn btn-sm btn-outline-secondary ms-3">Edit Global</a>
                                @else
                                    <div class="text-muted">No global MSP set.</div>
                                @endif
                            </div>
                            <div class="mt-4 text-muted small">
                                <p>The MSP enforces a minimum selling price. If a supplier rate is below the MSP, the system will raise it to the MSP.</p>
                            </div>
                        </div>

                        {{-- COUNTRY --}}
                        <div class="tab-pane" id="msp-country">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">Country MSP Rules</h6>
                                    <p class="text-muted small mb-0">These override the global MSP for a specific country.</p>
                                </div>
                                <button class="btn btn-outline-primary btn-sm" id="btn-add-country">+ Add Country MSP</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" id="mspCountryTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Country</th>
                                            <th class="text-center">MSP</th>
                                            <th class="text-center">Currency</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($countries as $m)
                                            <tr data-id="{{ $m->id }}" data-filter-text="{{ strtolower($m->country ?? '') }}">
                                                <td>{{ $m->country }}</td>
                                                <td class="text-center">${{ number_format($m->msp_amount, 2) }}</td>
                                                <td class="text-center">{{ $m->currency }}</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-link btn-sm js-edit-msp" data-id="{{ $m->id }}">Edit</button>
                                                    <button type="button" class="btn btn-link btn-sm text-danger js-delete-msp" data-id="{{ $m->id }}" data-name="{{ e($m->country) }}">Delete</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted small">No country MSP rules yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CITY --}}
                        <div class="tab-pane" id="msp-city">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">City MSP Rules</h6>
                                    <p class="text-muted small mb-0">These override country/global MSP for a specific city.</p>
                                </div>
                                <button class="btn btn-outline-primary btn-sm" id="btn-add-city">+ Add City MSP</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" id="mspCityTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>City</th>
                                            <th>Country</th>
                                            <th class="text-center">MSP</th>
                                            <th class="text-center">Currency</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cities as $m)
                                            <tr data-id="{{ $m->id }}" data-filter-text="{{ strtolower(($m->city ?? '') . ' ' . ($m->country ?? '')) }}">
                                                <td>{{ $m->city }}</td>
                                                <td>{{ $m->country }}</td>
                                                <td class="text-center">${{ number_format($m->msp_amount, 2) }}</td>
                                                <td class="text-center">{{ $m->currency }}</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-link btn-sm js-edit-msp" data-id="{{ $m->id }}">Edit</button>
                                                    <button type="button" class="btn btn-link btn-sm text-danger js-delete-msp" data-id="{{ $m->id }}" data-name="{{ e($m->city ?: $m->country) }}">Delete</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted small">No city MSP rules yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div> {{-- tab-content --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Add/Edit MSP (reused for create and edit) --}}
    <div class="modal fade" id="mspModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="mspForm" method="POST" action="{{ route('admin.msp.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="mspFormMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mspModalTitle">Add MSP Rule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Scope</label>
                            <select name="scope" id="mspScopeInput" class="form-select">
                                <option value="global">Global</option>
                                <option value="country">Country</option>
                                <option value="city">City</option>
                            </select>
                        </div>

                        <div class="mb-3" id="mspCountryDiv">
                            <label class="form-label">Country</label>
                            <select name="country" id="mspCountryInput" class="form-select">
                                <option value="">Select Country</option>
                                @foreach($uiCountries as $country)
                                    <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="mb-3" id="mspCityDiv">
                            <label class="form-label">City / Destination</label>
                            <select name="city" id="mspCityInput" class="form-select">
                                <option value="">Select City</option>
                                @foreach($uiDestinations as $dest)
                                    <option value="{{ $dest['code'] }}" data-country="{{ $dest['countryCode'] }}">
                                        {{ $dest['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">MSP Amount</label>
                            <input type="number" step="0.01" name="msp_amount" id="mspAmountInput" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" id="mspCurrencyInput" class="form-control" required value="USD">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="mspSaveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        // Elements
        const mspModalEl = document.getElementById('mspModal');
        const mspModal = (typeof bootstrap !== 'undefined' && mspModalEl) ? new bootstrap.Modal(mspModalEl) : null;
        const mspForm = document.getElementById('mspForm');
        const mspMethod = document.getElementById('mspFormMethod');
        const mspTitle = document.getElementById('mspModalTitle');

        const mspScope = document.getElementById('mspScopeInput');
        const mspCountryDiv = document.getElementById('mspCountryDiv');
        const mspCityDiv = document.getElementById('mspCityDiv');
        const mspCountry = document.getElementById('mspCountryInput');
        const mspCity = document.getElementById('mspCityInput');
        const mspAmount = document.getElementById('mspAmountInput');
        const mspCurrency = document.getElementById('mspCurrencyInput');

        const storeUrl = "{{ route('admin.msp.store') }}";
        const baseUrl = "{{ url('admin/pricing/msp') }}"; // use base + id for edit/delete
        const showBase = baseUrl.replace(/\/+$/, '') + '/'; // + id + '/json'

        // Small helper - safe DOM text setter
        function safeSet(el, value) { if (!el) return; el.value = value ?? ''; }

        // toggle scope fields
        function toggleScopeFields() {
            if (!mspScope) return;
            const val = mspScope.value;
            if (val === 'global') {
                if (mspCountryDiv) mspCountryDiv.style.display = 'none';
                if (mspCityDiv) mspCityDiv.style.display = 'none';
                if (mspCountry) mspCountry.value = '';
                if (mspCity) mspCity.value = '';
            } else if (val === 'country') {
                if (mspCountryDiv) mspCountryDiv.style.display = '';
                if (mspCityDiv) mspCityDiv.style.display = 'none';
                if (mspCity) mspCity.value = '';
            } else {
                if (mspCountryDiv) mspCountryDiv.style.display = '';
                if (mspCityDiv) mspCityDiv.style.display = '';
            }
        }
        if (mspScope) mspScope.addEventListener('change', toggleScopeFields);

        // safe modal show wrapper
        function showModal() {
            if (!mspModal) {
                console.warn('Bootstrap modal instance not found - cannot show modal. Ensure bootstrap.js is loaded.');
                alert('Modal cannot be shown: missing JavaScript dependency (bootstrap). Check console.');
                return;
            }
            mspModal.show();
        }

        // open modal helper (create/edit)
        function openMspModal(mode = 'create', presetScope = null, editId = null) {
            if (!mspForm) return console.error('mspForm not found in DOM');
            try {
                mspForm.reset();
            } catch (e) { /* ignore */ }

            if (mspMethod) mspMethod.value = 'POST';
            mspForm.action = storeUrl;
            if (mspTitle) mspTitle.textContent = mode === 'create' ? 'Add MSP Rule' : 'Edit MSP Rule';

            if (presetScope && mspScope) mspScope.value = presetScope;
            else if (mspScope) mspScope.value = 'global';

            toggleScopeFields();

            if (mode === 'edit' && editId) {
                if (mspMethod) mspMethod.value = 'PUT';
                mspForm.action = baseUrl.replace(/\/+$/, '') + '/' + encodeURIComponent(editId);
                // fetch JSON to populate
                fetch(showBase + encodeURIComponent(editId) + '/json', { headers: { 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('Fetch failed: ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (mspScope) mspScope.value = data.scope || 'global';
                        safeSet(mspCountry, data.country || '');
                        safeSet(mspCity, data.city || '');
                        safeSet(mspAmount, data.msp_amount ?? '');
                        safeSet(mspCurrency, data.currency || 'USD');
                        toggleScopeFields();
                        showModal();
                    })
                    .catch(err => {
                        console.error('Failed to load MSP for edit:', err);
                        alert('Failed to load MSP rule data for editing. See console for details.');
                    });
            } else {
                // create
                if (mspAmount) mspAmount.value = '';
                if (mspCurrency) mspCurrency.value = 'USD';
                toggleScopeFields();
                showModal();
            }
        }

        // Attach direct listeners if elements exist
        function attachIfExists(selectorOrEl, handler) {
            if (!selectorOrEl) return;
            const el = (typeof selectorOrEl === 'string') ? document.getElementById(selectorOrEl) : selectorOrEl;
            if (el) el.addEventListener('click', handler);
        }

        attachIfExists('btn-add-msp', function () { openMspModal('create'); });
        attachIfExists('btn-add-country', function () { openMspModal('create', 'country'); });
        attachIfExists('btn-add-city', function () { openMspModal('create', 'city'); });

        // fallback event delegation — works if buttons are added later or IDs differ
        document.addEventListener('click', function (ev) {
            const btn = ev.target.closest && ev.target.closest('[id^="btn-add-"], .js-edit-msp');
            if (!btn) return;

            // Add country
            if (btn.id === 'btn-add-country') {
                ev.preventDefault();
                console.debug('Delegated: open add country');
                openMspModal('create', 'country');
                return;
            }

            // Add city
            if (btn.id === 'btn-add-city') {
                ev.preventDefault();
                console.debug('Delegated: open add city');
                openMspModal('create', 'city');
                return;
            }

            // Add generic MSP
            if (btn.id === 'btn-add-msp') {
                ev.preventDefault();
                console.debug('Delegated: open add msp');
                openMspModal('create');
                return;
            }

            // Edit button (delegated) - expects data-id on element
            if (btn.classList && btn.classList.contains('js-edit-msp')) {
                ev.preventDefault();
                const id = btn.dataset && btn.dataset.id;
                if (!id) return alert('Missing id for edit');
                openMspModal('edit', null, id);
                return;
            }
        });

        // Edit buttons direct (if present at page load)
        document.querySelectorAll('.js-edit-msp').forEach(function(btn) {
            btn.addEventListener('click', function (ev) {
                ev.preventDefault();
                const id = this.dataset.id;
                if (!id) return alert('Missing id');
                openMspModal('edit', null, id);
            });
        });

        // Delete handler (method spoofing)
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return meta.getAttribute('content');
            const tokenInput = document.querySelector('input[name="_token"]');
            return tokenInput ? tokenInput.value : '';
        }
        async function deleteMsp(url, name, btn) {
            if (!confirm(`Delete ${name}? This action cannot be undone.`)) return;
            btn.disabled = true;
            const orig = btn.innerHTML;
            btn.innerHTML = 'Deleting...';
            try {
                const fd = new FormData();
                fd.append('_method', 'DELETE');
                const token = getCsrfToken();
                if (token) fd.append('_token', token);

                const res = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                if (res.ok) location.reload();
                else {
                    const text = await res.text();
                    console.error('Delete failed', text);
                    alert('Delete failed — see console');
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            } catch (err) {
                console.error(err);
                alert('Network error during delete');
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        }

        document.querySelectorAll('.js-delete-msp').forEach(function(btn) {
            btn.addEventListener('click', function (ev) {
                ev.preventDefault();
                const id = this.dataset.id;
                const name = this.dataset.name || 'this rule';
                if (!id) return alert('Missing id');
                const url = baseUrl.replace(/\/+$/, '') + '/' + encodeURIComponent(id);
                deleteMsp(url, name, this);
            });
        });

        // final console debug showing listeners wired
        console.info('MSP UI script loaded. modal?', !!mspModal, 'scopeInput?', !!mspScope);

        // ensure initial toggle state
        if (mspScope) toggleScopeFields();

    } catch (err) {
        console.error('Unexpected error in MSP script:', err);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const countrySelect = document.getElementById('mspCountryInput');
    const citySelect = document.getElementById('mspCityInput');

    countrySelect.addEventListener('change', function () {
        const selectedCountry = this.value;

        // Reset City dropdown
        citySelect.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return; // keep placeholder
            opt.hidden = opt.getAttribute('data-country') !== selectedCountry;
        });

        citySelect.value = "";
    });
});
</script>

</x-app-layout>
