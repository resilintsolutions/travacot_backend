{{-- ===========================
    COUNTRY RULE LIST
=========================== --}}
<div class="card mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-1">Country Margin Rules</h6>
            <p class="text-muted small mb-0">
                Country-level rules override global margins.
            </p>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-country">
            + Add Country Rule
        </button>
    </div>

    <div class="card-body">

        {{-- Search --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control js-filter-input"
                           placeholder="Search country..."
                           data-target-table="#countryRulesTable">
                </div>
            </div>
        </div>

        {{-- Country Table --}}
        <div class="table-responsive">
            <table class="table table-sm align-middle" id="countryRulesTable">
                <thead class="table-light">
                    <tr>
                        <th>Country</th>
                        <th class="text-center" style="width:140px;">Margin (%)</th>
                        <th class="text-center" style="width:150px;">Status</th>
                        <th class="text-end" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($countryRules as $rule)
                        @php $enabled = $rule->is_enabled ?? 1; @endphp

                        <tr data-filter-text="{{ strtolower($rule->country) }}">
                            <td>{{ $rule->country }}</td>

                            <td class="text-center">
                                {{ number_format($rule->default_margin_percent, 2) }}%
                            </td>

                            {{-- Enable/Disable --}}
                            <td class="text-center">
                                <label class="switch">
                                    <input type="checkbox"
                                           class="toggle-rule"
                                           data-id="{{ $rule->id }}"
                                           {{ $enabled ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>

                                <div class="small mt-1 {{ $enabled ? 'text-success' : 'text-danger' }}">
                                    {{ $enabled ? '🟢 Enabled' : '🔴 Disabled' }}
                                </div>
                            </td>

                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-link btn-sm text-decoration-none js-edit-rule"
                                        data-id="{{ $rule->id }}"
                                        data-scope="country">
                                    Edit
                                </button>

                                <button type="button"
                                        class="btn btn-link btn-sm text-danger text-decoration-none js-delete-rule"
                                        data-url="{{ route('admin.margin-rules.destroy', $rule->id) }}"
                                        data-id="{{ $rule->id }}"
                                        data-name="{{ $rule->country }}">
                                    Delete
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted small">
                                No country rules created yet.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>
</div>
