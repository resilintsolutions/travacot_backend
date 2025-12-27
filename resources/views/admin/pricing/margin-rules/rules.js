/* ============================================================
   Helper: CSRF token
============================================================ */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

/* ============================================================
   Helper: POST with method spoofing
============================================================ */
async function post(url, data = {}) {
    return fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(data)
    }).then(r => r.json());
}

/* ============================================================
   DELETE RULE (AJAX)
============================================================ */
document.addEventListener("click", function(e) {
    if (!e.target.classList.contains("js-delete-rule")) return;

    const btn = e.target;
    const url = btn.dataset.url;
    const name = btn.dataset.name || "this rule";

    if (!confirm(`Delete ${name}?`)) return;

    btn.disabled = true;
    btn.innerText = "Deleting...";

    const data = { _method: "DELETE" };

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Accept": "application/json"
        },
        body: new FormData(Object.assign(document.createElement('form'), { _method: "DELETE" }))
    })
        .then(r => location.reload())
        .catch(() => {
            alert("Failed to delete rule.");
            btn.disabled = false;
        });
});

/* ============================================================
   ENABLE / DISABLE TOGGLE (AJAX)
============================================================ */
document.addEventListener("change", function(e) {
    if (!e.target.classList.contains("toggle-rule")) return;

    const id = e.target.dataset.id;
    const enabled = e.target.checked ? 1 : 0;

    post(`/admin/pricing/margin-rules/${id}/toggle`, { is_enabled: enabled })
        .then(() => location.reload())
        .catch(() => alert("Toggle error"));
});

/* ============================================================
   FILTER TABLE ROWS (Search)
============================================================ */
document.addEventListener("input", function(e) {
    if (!e.target.classList.contains("js-filter-input")) return;

    const value = e.target.value.toLowerCase();
    const table = document.querySelector(e.target.dataset.targetTable);

    if (!table) return;

    table.querySelectorAll("tbody tr").forEach(row => {
        const text = row.dataset.filterText || "";
        row.style.display = text.includes(value) ? "" : "none";
    });
});

/* ============================================================
   COUNTRY MODAL: CREATE / EDIT
============================================================ */
const countryModalEl = document.getElementById("countryRuleModal");
const countryModal = countryModalEl ? new bootstrap.Modal(countryModalEl) : null;

function openCountryModal(mode, id = null) {
    const form = document.getElementById("countryRuleForm");
    const method = document.getElementById("countryFormMethod");
    const title = document.getElementById("countryRuleTitle");
    const marginInput = document.getElementById("countryMarginInput");
    const enabledInput = document.getElementById("countryEnabledInput");
    const countrySelect = document.getElementById("countryInput");

    form.reset();
    method.value = "POST";
    form.action = "/admin/pricing/margin-rules";

    title.innerText = mode === "create" ? "Add Country Rule" : "Edit Country Rule";

    const globalMargin = parseFloat(document.body.dataset.globalDefaultMargin || 0);
    marginInput.value = globalMargin;
    enabledInput.checked = true;

    if (mode === "edit" && id) {
        method.value = "PUT";
        form.action = `/admin/pricing/margin-rules/${id}`;

        fetch(`/admin/pricing/margin-rules/${id}/json`)
            .then(r => r.json())
            .then(rule => {
                countrySelect.value = rule.country;
                marginInput.value = rule.default_margin_percent;
                enabledInput.checked = rule.is_enabled == 1;
            });
    }

    countryModal.show();
}

document.addEventListener("click", function(e) {
    if (e.target.id === "btn-add-country") {
        openCountryModal("create");
    }
    if (e.target.classList.contains("js-edit-rule") && e.target.dataset.scope === "country") {
        openCountryModal("edit", e.target.dataset.id);
    }
});

/* ============================================================
   CITY MODAL: CREATE / EDIT
============================================================ */
const cityModalEl = document.getElementById("cityRuleModal");
const cityModal = cityModalEl ? new bootstrap.Modal(cityModalEl) : null;

let DESTS = window.__DESTS__ || [];

/* ---------- AUTOCOMPLETE ---------- */
const cityInput = document.getElementById("cityInput");
const cityCodeInput = document.getElementById("cityCodeInput");
const cityDropdown = document.getElementById("cityDropdown");
const cityCountrySelect = document.getElementById("cityCountryInput");

function escapeHTML(str) {
    return str.replace(/[&<>"'`=\/]/g, s => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;",
        "'": "&#39;", "/": "&#x2F;", "`": "&#x60;", "=": "&#x3D;"
    }[s]));
}

function renderCityDropdown(results) {
    cityDropdown.innerHTML = "";
    if (!results.length) {
        cityDropdown.style.display = "none";
        return;
    }
    results.forEach((item, idx) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "list-group-item list-group-item-action";
        btn.innerHTML = `
            <strong>${escapeHTML(item.name)}</strong>
            <span class="text-muted">(${item.code})</span><br>
            <small>${item.countryCode}</small>
        `;
        btn.onclick = () => chooseCity(item);
        cityDropdown.appendChild(btn);
    });
    cityDropdown.style.display = "block";
}

function chooseCity(item) {
    cityInput.value = `${item.name} (${item.code})`;
    cityCodeInput.value = item.code;
    cityCountrySelect.value = item.countryCode;
    cityDropdown.style.display = "none";
}

cityInput?.addEventListener("input", function() {
    const q = this.value.trim().toLowerCase();
    if (!q) {
        cityDropdown.style.display = "none";
        return;
    }

    const results = DESTS.filter(d =>
        d.name.toLowerCase().includes(q) ||
        d.code.toLowerCase().includes(q) ||
        d.countryCode.toLowerCase().includes(q)
    ).slice(0, 20);

    renderCityDropdown(results);
});

/* ---------- OPEN CITY MODAL ---------- */
function openCityModal(mode, id = null) {
    const form = document.getElementById("cityRuleForm");
    const method = document.getElementById("cityFormMethod");
    const title = document.getElementById("cityRuleTitle");
    const marginInput = document.getElementById("cityMarginInput");
    const enabledInput = document.getElementById("cityEnabledInput");

    form.reset();
    method.value = "POST";
    form.action = "/admin/pricing/margin-rules";

    title.innerText = mode === "create" ? "Add City Rule" : "Edit City Rule";

    const globalMargin = parseFloat(document.body.dataset.globalDefaultMargin || 0);
    marginInput.value = globalMargin;

    if (mode === "edit" && id) {
        method.value = "PUT";
        form.action = `/admin/pricing/margin-rules/${id}`;

        fetch(`/admin/pricing/margin-rules/${id}/json`)
            .then(r => r.json())
            .then(rule => {
                const dest = DESTS.find(d => d.code === rule.city);
                cityInput.value = dest ? `${dest.name} (${dest.code})` : rule.city;
                cityCodeInput.value = rule.city;
                cityCountrySelect.value = rule.country;
                marginInput.value = rule.default_margin_percent;
                enabledInput.checked = rule.is_enabled == 1;
            });
    }

    cityModal.show();
}

document.addEventListener("click", function(e) {
    if (e.target.id === "btn-add-city") {
        openCityModal("create");
    }
    if (e.target.classList.contains("js-edit-rule") && e.target.dataset.scope === "city") {
        openCityModal("edit", e.target.dataset.id);
    }
});

/* ============================================================
   CLICK OUTSIDE AUTOCOMPLETE
============================================================ */
document.addEventListener("click", function(e) {
    if (
        cityDropdown &&
        !cityDropdown.contains(e.target) &&
        e.target !== cityInput
    ) {
        cityDropdown.style.display = "none";
    }
});
