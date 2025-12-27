<script>
document.addEventListener('DOMContentLoaded', function() {

    const input = document.getElementById('cityInput');
    const hidden = document.getElementById('cityCodeInput');
    const dropdown = document.getElementById('cityDropdown');

    if (!input || !hidden || !dropdown) return;

    const DESTINATIONS = {!! json_encode(array_values($destinations)) !!};
    const items = DESTINATIONS.map(d => ({
        code: (d.code || d.destinationCode || "").toUpperCase(),
        name: d.name || "",
        country: (d.countryCode || d.country || "").toUpperCase(),
    })).filter(d => d.code && d.name);

    let results = [];
    let activeIndex = -1;

    function clear() {
        dropdown.innerHTML = "";
        dropdown.style.display = "none";
    }

    function show(items) {
        dropdown.innerHTML = "";
        items.forEach((it, idx) => {
            const div = document.createElement('button');
            div.type = "button";
            div.className = "list-group-item list-group-item-action";
            div.dataset.index = idx;
            div.innerHTML = `<b>${it.name}</b> <small>(${it.code})</small><br><small>${it.country}</small>`;
            div.onclick = () => choose(idx);
            dropdown.appendChild(div);
        });
        dropdown.style.display = "block";
    }

    function choose(i) {
        const it = results[i];
        input.value = `${it.name} (${it.code})`;
        hidden.value = it.code;
        clear();
    }

    input.addEventListener('input', function() {
        const q = input.value.trim().toLowerCase();
        if (q.length < 1) return clear();

        results = items.filter(
            it => it.name.toLowerCase().includes(q) ||
                  it.code.toLowerCase().includes(q) ||
                  it.country.toLowerCase().includes(q)
        ).slice(0, 20);

        if (results.length) show(results);
        else clear();
    });

    document.addEventListener('click', e => {
        if (!dropdown.contains(e.target) && !input.contains(e.target)) clear();
    });

});
</script>
