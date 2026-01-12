// resources/js/admin.js
import './bootstrap';

/**
 * Utility helpers
 */
const getToken = () => localStorage.getItem('token') || '';
const baseUrl = import.meta.env.VITE_API_BASE || '';

function safeText(node, text) {
  if (!node) return;
  node.textContent = text ?? '';
}

async function safeJsonResponse(res) {
  const text = await res.text();
  try {
    return text ? JSON.parse(text) : {};
  } catch (err) {
    // fallback: return raw text under error field
    return { success: false, error: { message: 'Invalid JSON response', raw: text } };
  }
}

async function apiFetch(path, opts = {}) {
  const token = getToken();
  const headers = Object.assign(
    { Accept: 'application/json' },
    opts.headers || {}
  );
  if (token) headers.Authorization = 'Bearer ' + token;

  const res = await fetch(baseUrl + path, Object.assign({}, opts, { headers }));
  const json = await safeJsonResponse(res);
  if (!res.ok) {
    const message = json?.message || json?.error?.message || res.statusText || 'Request failed';
    const err = new Error(message);
    err.status = res.status;
    err.response = json;
    throw err;
  }
  return json;
}

async function apiGet(path) {
  return await apiFetch(path, { method: 'GET' });
}

async function apiPost(path, body) {
  return await apiFetch(path, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
}

/**
 * Application logic
 */
async function loadKpis() {
  try {
    const data = await apiGet('/api/dashboard/kpis?range=week');
    safeText(document.getElementById('totalBookings'), data.totalBookings ?? '--');
    safeText(document.getElementById('totalRevenue'), data.totalRevenue ?? '--');
    safeText(document.getElementById('avgBookingValue'), data.averageBookingValue ?? '--');
  } catch (err) {
    console.error('loadKpis error', err);
  }
}

async function loadHotels() {
  try {
    const res = await apiGet('/api/hotels?limit=50');
    const target = document.getElementById('hotels-list');
    if (!target) return;
    target.innerHTML = '';
    (res.data || []).forEach(h => {
      const el = document.createElement('div');
      // prefer textContent to avoid XSS
      const name = document.createElement('strong'); name.textContent = h.name || '';
      el.appendChild(name);
      el.insertAdjacentHTML('beforeend', ` — ${escapeHtml(h.country)}${h.city ? ', ' + escapeHtml(h.city) : ''} — vendor: ${escapeHtml(h.vendor)}`);
      target.appendChild(el);
    });
  } catch (err) {
    console.error('loadHotels error', err);
  }
}

/** Escape helper for small HTML injections (used sparingly) */
function escapeHtml(s) {
  if (s === undefined || s === null) return '';
  return String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

/**
 * Supplier / Hotelbeds related functions
 */
async function supplierSearch() {
  try {
    const checkIn = document.getElementById('hb_checkIn')?.value;
    const checkOut = document.getElementById('hb_checkOut')?.value;
    const url = `/api/hotelbeds/hotels?checkIn=${encodeURIComponent(checkIn)}&checkOut=${encodeURIComponent(checkOut)}`;
    const json = await apiGet(url);
    if (!json.success && json.success !== undefined) {
      alert('Error fetching supplier hotels: ' + (json.error?.message || JSON.stringify(json)));
      return;
    }
    renderSupplierHotels(json.data || []);
  } catch (err) {
    console.error('supplierSearch error', err);
    alert('Supplier search failed: ' + (err.message || 'unknown error'));
  }
}

function renderSupplierHotels(hotels = []) {
  const target = document.getElementById('supplierHotelsList');
  if (!target) return;
  target.innerHTML = '';
  if (!hotels.length) {
    target.innerHTML = '<em>No hotels returned</em>';
    return;
  }

  hotels.forEach(h => {
    const card = document.createElement('div');
    card.className = 'supplier-card';
    const header = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = h.name || '';
    header.appendChild(title);
    header.insertAdjacentHTML('beforeend', ` — ${escapeHtml(h.country)} / ${escapeHtml(h.city)}`);
    card.appendChild(header);

    const roomsContainer = document.createElement('div');
    (h.rooms || []).forEach((r) => {
      const rdiv = document.createElement('div');
      const rtitle = document.createElement('strong');
      rtitle.textContent = r.name || '';
      rdiv.appendChild(rtitle);

      (r.rates || []).forEach(rt => {
        const small = document.createElement('small');
        const net = escapeHtml(rt.net ?? rt.price ?? '?');
        const cur = escapeHtml(rt.currency ?? '');
        small.innerHTML = ` ${net} ${cur} `;
        const btn = document.createElement('button');
        btn.className = 'import-rate btn btn-sm';
        btn.textContent = 'Import';
        btn.dataset.vendor = h.vendor_id ?? h.vendor ?? '';
        // store a minimal, safe payload (not raw full object) to avoid large attributes
        btn.dataset.hotel = encodeURIComponent(JSON.stringify({ id: h.id, name: h.name }));
        btn.dataset.rate = rt.rateKey ?? rt.rateKeyId ?? '';
        small.appendChild(btn);
        rdiv.appendChild(small);
      });

      roomsContainer.appendChild(rdiv);
    });

    card.appendChild(roomsContainer);
    target.appendChild(card);
  });

  // attach import handlers
  document.querySelectorAll('button.import-rate').forEach(btn => {
    btn.removeEventListener('click', importRateHandler); // safe remove if re-rendering
    btn.addEventListener('click', importRateHandler);
  });
}

async function importRateHandler(e) {
  try {
    const vendorId = e.currentTarget.dataset.vendor;
    const hotelRaw = JSON.parse(decodeURIComponent(e.currentTarget.dataset.hotel || '{}'));
    const rateKey = e.currentTarget.dataset.rate;
    const resp = await apiPost('/api/hotelbeds/import', { vendor_id: vendorId, hotel: hotelRaw, rateKey });
    if (resp.success) {
      alert('Imported hotel: ' + (resp.hotel?.name ?? resp.hotel?.id ?? 'OK'));
    } else {
      alert('Import failed: ' + JSON.stringify(resp));
    }
  } catch (err) {
    console.error('importRateHandler error', err);
    alert('Import failed: ' + (err.message || 'unknown'));
  }
}

/**
 * Inventory functions
 */
async function loadInventory(query = '') {
  try {
    const q = encodeURIComponent(query || '');
    const json = await apiGet(`/api/inventory/hotels?query=${q}`);
    const container = document.getElementById('inv_list');
    if (!container) return;
    container.innerHTML = '';
    const items = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
    items.forEach(h => {
      const el = document.createElement('div');
      el.className = 'inventory-item';
      const name = document.createElement('strong'); name.textContent = h.name || '';
      el.appendChild(name);
      el.insertAdjacentHTML('beforeend', ` — ${escapeHtml(h.country)}${h.city ? ' / ' + escapeHtml(h.city) : ''}`);
      const importBtn = document.createElement('button');
      importBtn.className = 'btn-import';
      importBtn.dataset.id = h.id;
      importBtn.textContent = 'Import';
      const pinBtn = document.createElement('button');
      pinBtn.className = 'btn-pin';
      pinBtn.dataset.id = h.id;
      pinBtn.textContent = 'Pin';
      el.appendChild(importBtn);
      el.appendChild(pinBtn);
      container.appendChild(el);
    });

    // import handlers
    document.querySelectorAll('.btn-import').forEach(b => {
      b.removeEventListener('click', localImportHandler);
      b.addEventListener('click', localImportHandler);
    });
    // pin handlers
    document.querySelectorAll('.btn-pin').forEach(b => {
      b.removeEventListener('click', pinHandler);
      b.addEventListener('click', pinHandler);
    });
  } catch (err) {
    console.error('loadInventory error', err);
  }
}

async function localImportHandler(e) {
  alert('Hotel already local — use import from supplier section for supplier hotels.');
}

async function pinHandler(e) {
  try {
    const hotelId = e.currentTarget.dataset.id;
    const j = await apiPost('/api/inventory/pin', { hotel_id: hotelId });
    if (j.success) alert('Pinned');
    else alert('Pin failed: ' + JSON.stringify(j));
  } catch (err) {
    console.error('pinHandler error', err);
    alert('Pin action failed: ' + (err.message || 'unknown'));
  }
}

/**
 * DOM wiring
 */
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('totalBookings')) loadKpis();
  if (document.getElementById('hotels-list')) loadHotels();

  const refreshBtn = document.getElementById('refreshBtn');
  if (refreshBtn) refreshBtn.addEventListener('click', loadKpis);

  const qf = document.getElementById('quoteForm');
  if (qf) {
    qf.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(qf);
      const hotelId = fd.get('hotelId');
      const body = {
        roomId: Number(fd.get('roomId')) || null,
        checkIn: fd.get('checkIn'),
        checkOut: fd.get('checkOut'),
        quantity: Number(fd.get('quantity')) || 1
      };
      try {
        const res = await apiPost(`/api/hotels/${hotelId}/rooms/price`, body);
        const resultNode = document.getElementById('quoteResult');
        safeText(resultNode, JSON.stringify(res, null, 2));
      } catch (err) {
        console.error('quote submit error', err);
        alert('Quote failed: ' + (err.message || 'unknown'));
      }
    });
  }

  document.getElementById('hb_search')?.addEventListener('click', supplierSearch);
  document.getElementById('inv_search')?.addEventListener('click', () => loadInventory(document.getElementById('inv_query')?.value || ''));

  // Laravel Echo realtime listeners (if Echo is available)
  if (window.Echo) {
    try {
      window.Echo.channel('dashboard')
        .listen('.kpis.updated', (payload) => {
          const k = payload.kpis ?? payload;
          safeText(document.getElementById('totalBookings'), k.totalBookings ?? '--');
          safeText(document.getElementById('totalRevenue'), k.totalRevenue ?? '--');
          safeText(document.getElementById('avgBookingValue'), k.averageBookingValue ?? '--');
        });
      window.Echo.channel('bookings').listen('.booking.created', (payload) => console.log('New booking', payload));
      window.Echo.channel('imports').listen('.job.updated', (payload) => console.log('Import job', payload));
    } catch (err) {
      console.warn('Echo listeners failed to attach', err);
    }
  }
});
