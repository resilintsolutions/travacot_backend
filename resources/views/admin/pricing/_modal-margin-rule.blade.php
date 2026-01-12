{{-- resources/views/admin/pricing/_modal-rule.blade.php --}}
@push('styles')
<style>
  .modal .form-control { min-height: 38px; }
</style>
@endpush

<div class="modal fade" id="ruleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.margin-rules.store') }}">
        @csrf
        <input type="hidden" name="scope" value="country">

        <div class="modal-header">
          <h5 class="modal-title">Add Rule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Scope</label>
              <select class="form-select" name="scope_display" disabled>
                <option>Use the Add button to choose scope</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Country (for Country or City scope)</label>
              <input name="country" type="text" class="form-control" placeholder="e.g. Lebanon">
            </div>

            <div class="col-md-6">
              <label class="form-label">City (only for City scope)</label>
              <input name="city" type="text" class="form-control" placeholder="e.g. Beirut">
            </div>

            <div class="col-md-6">
              <label class="form-label">Default Margin (%)</label>
              <input name="default_margin_percent" type="number" step="0.01" class="form-control" value="10" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Min Margin (%)</label>
              <input name="min_margin_percent" type="number" step="0.01" class="form-control" value="5" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Max Margin (%)</label>
              <input name="max_margin_percent" type="number" step="0.01" class="form-control" value="25" required>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Confirm and add rule</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // keep a hidden real scope field (overwrites the value before submit)
  document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('ruleModal');
    if (!modal) return;
    // ensure a real hidden field exists or create one
    if (!modal.querySelector('input[name="scope"]')) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'scope';
      hidden.value = 'country';
      modal.querySelector('form').appendChild(hidden);
    }
    modal.addEventListener('show.bs.modal', function (e) {
      const trigger = e.relatedTarget;
      const scope = trigger?.dataset?.scope || 'country';
      modal.querySelector('input[name="scope"]').value = scope;
      // update disabled display select to show scope string
      const disp = modal.querySelector('select[name="scope_display"]');
      if (disp) {
        disp.innerHTML = `<option>${scope.charAt(0).toUpperCase()+scope.slice(1)}</option>`;
      }
    });
  });
</script>
@endpush
