<x-app-layout>
    <div class="container py-4">
        <div class="inv-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="inv-header-title">Hotel</div>
                    <h2 class="inv-page-title">{{ $hotel->name }}</h2>
                    <div class="text-muted" style="font-size:13px;">
                        Vendor: <strong>{{ $hotel->vendor ?? '—' }}</strong>
                        &nbsp; • &nbsp;
                        Vendor ID: <strong>{{ $hotel->vendor_id ?? '—' }}</strong>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.inventory.hotels_list') }}" class="btn btn-outline-secondary btn-sm">← Back to list</a>
                </div>
            </div>

            <div class="row">
                {{-- Left column: details --}}
                <div class="col-lg-4 mb-3">
                    <div class="p-3" style="background:#fff;border-radius:12px;">
                        <h5 class="mb-1">{{ $hotel->name }}</h5>
                        <div class="text-muted mb-2">
                            {{ trim(($hotel->city ?? '') . ($hotel->city && $hotel->country ? ', ' : '') . ($hotel->country ?? '')) }}
                        </div>

                        <p class="small text-muted">
                            <strong>Status:</strong>
                            <span class="status-pill {{ $hotel->status === 'active' ? 'status-pill--active' : ($hotel->status === 'inactive' ? 'status-pill--inactive' : 'status-pill--suspended') }}">
                                <span class="status-dot"
                                      style="background:{{ $hotel->status === 'active' ? '#16a34a' : ($hotel->status === 'inactive' ? '#b91c1c' : '#f59e0b') }}"></span>
                                {{ ucfirst($hotel->status) }}
                            </span>
                        </p>

                        <p class="small">
                            <strong>Lowest rate:</strong>
                            {{ $hotel->lowest_rate ? ($hotel->currency . ' ' . $hotel->lowest_rate) : '—' }}
                        </p>

                        {{-- Description --}}
                        <h6 class="mt-3">Description</h6>
                        <div class="small text-muted" style="max-height:140px; overflow:auto;">
                            {!! nl2br(e($hotel->description ?? (data_get($hotel->meta,'description') ?? 'No description available.'))) !!}
                        </div>

                        @php
                            $meta = $hotel->meta ?? [];
                        @endphp

                        {{-- Metadata table --}}
                        @if(!empty($meta))
                            <hr class="mt-3 mb-2">

                            <h6 class="mb-2">Metadata</h6>
                            <table class="table table-sm table-bordered mb-3" style="font-size:12px;">
                                <tbody>
                                <tr>
                                    <th style="width:40%;">Hotel Code</th>
                                    <td>{{ data_get($meta, 'code', '—') }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>
                                        {{ data_get($meta, 'categoryName', '—') }}
                                        @if(data_get($meta,'categoryCode'))
                                            ({{ data_get($meta, 'categoryCode') }})
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Destination</th>
                                    <td>
                                        {{ data_get($meta, 'destinationName', '—') }}
                                        @if(data_get($meta,'destinationCode'))
                                            ({{ data_get($meta, 'destinationCode') }})
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Zone</th>
                                    <td>
                                        {{ data_get($meta, 'zoneName', '—') }}
                                        @if(data_get($meta,'zoneCode'))
                                            ({{ data_get($meta, 'zoneCode') }})
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Latitude</th>
                                    <td>{{ data_get($meta, 'latitude', '—') }}</td>
                                </tr>
                                <tr>
                                    <th>Longitude</th>
                                    <td>{{ data_get($meta, 'longitude', '—') }}</td>
                                </tr>
                                <tr>
                                    <th>Min rate</th>
                                    <td>
                                        @if(data_get($meta,'minRate'))
                                            {{ data_get($meta,'currency','') }} {{ data_get($meta,'minRate') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Max rate</th>
                                    <td>
                                        @if(data_get($meta,'maxRate'))
                                            {{ data_get($meta,'currency','') }} {{ data_get($meta,'maxRate') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            {{-- Rooms & Rates --}}
                            @if(!empty(data_get($meta, 'rooms')))
                                <h6 class="mb-2">Rooms &amp; Rates</h6>

                                <div style="max-height:220px; overflow:auto;">
                                    @foreach(data_get($meta,'rooms',[]) as $room)
                                        <div class="border rounded mb-2 p-2" style="background:#fafafa;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong style="font-size:12px;">
                                                    {{ $room['name'] ?? 'Room' }}
                                                    @if(!empty($room['code']))
                                                        ({{ $room['code'] }})
                                                    @endif
                                                </strong>
                                                <span class="badge bg-light text-muted border"
                                                      style="font-size:10px;">{{ count($room['rates'] ?? []) }} rates</span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0" style="font-size:11px;">
                                                    <thead>
                                                    <tr>
                                                        <th>Board</th>
                                                        <th>Net</th>
                                                        <th>Adults</th>
                                                        <th>Children</th>
                                                        <th>Payment</th>
                                                        <th>Cancellation from</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($room['rates'] ?? [] as $rate)
                                                        <tr>
                                                            <td>{{ $rate['boardName'] ?? ($rate['boardCode'] ?? '—') }}</td>
                                                            <td>
                                                                {{ data_get($meta,'currency') }}
                                                                {{ $rate['net'] ?? '—' }}
                                                            </td>
                                                            <td>{{ $rate['adults'] ?? '—' }}</td>
                                                            <td>{{ $rate['children'] ?? '—' }}</td>
                                                            <td>{{ $rate['paymentType'] ?? '—' }}</td>
                                                            <td>
                                                                {{ $rate['cancellationPolicies'][0]['from'] ?? '—' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Optional: raw JSON collapsible for debugging --}}
                            <div class="mt-2">
                                <a class="small text-primary" data-bs-toggle="collapse" href="#rawMetaJson" role="button"
                                   aria-expanded="false" aria-controls="rawMetaJson">
                                    Show raw JSON
                                </a>
                                <div class="collapse mt-1" id="rawMetaJson">
                                    <pre
                                        style="max-height:140px; overflow:auto; background:#f7f7f8; padding:8px; border-radius:6px; font-size:11px;">
{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}
                                    </pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right column: media gallery --}}
                <div class="col-lg-8">

                    <div class="mb-3">
                        <div class="inv-header-title">Media</div>
                        <div class="h5 mb-2">Gallery</div>
                        <div class="text-muted small mb-3">
                            Images and media imported from suppliers or manually uploaded.
                            Click any image to view full size.
                        </div>
                    </div>

                    {{-- Upload form --}}
                    <div class="mb-3">
                        <form id="media-upload-form"
                              action="{{ route('admin.hotels.media.store', $hotel) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex gap-2">
                                <input type="file"
                                       name="file"
                                       accept="image/*,video/*,application/pdf"
                                       class="form-control form-control-sm"
                                       required>
                                <input type="hidden" name="collection" value="images">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Upload
                                </button>
                            </div>
                            <div class="small text-muted mt-1">Max file size: 5MB</div>
                        </form>
                    </div>

                    {{-- Gallery grid --}}
                    <div class="inv-table-wrapper">
                        @if($media->isEmpty())
                            <div id="no-media-message" class="p-4 text-center text-muted">
                                No media available for this hotel.
                            </div>
                        @else
                            <div class="row g-2" id="media-grid">
                                @foreach($media as $m)
                                    <div class="col-6 col-md-4 col-lg-3 media-card-wrapper" id="media-card-{{ $m->id }}">
                                        <div class="card {{ $m->is_featured ? 'border-success' : '' }}" style="border-radius:10px; overflow:hidden;">
                                            @if(\Illuminate\Support\Str::startsWith($m->mime_type ?? '', 'image'))
                                                <a href="{{ $m->public_url }}" target="_blank">
                                                    <img src="{{ $m->public_url }}" class="img-fluid"
                                                         style="width:100%; height:160px; object-fit:cover;">
                                                </a>
                                            @elseif(\Illuminate\Support\Str::startsWith($m->mime_type ?? '', 'video'))
                                                <a href="{{ $m->public_url }}" target="_blank">
                                                    <video style="width:100%; height:160px; object-fit:cover;" muted>
                                                        <source src="{{ $m->public_url }}" type="{{ $m->mime_type }}">
                                                    </video>
                                                </a>
                                            @else
                                                <div
                                                    style="width:100%; height:160px; display:flex; align-items:center; justify-content:center; background:#f3f4f6;">
                                                    <a href="{{ $m->public_url }}" target="_blank"
                                                       class="text-decoration-none text-center">
                                                        <div class="text-muted small">Download file</div>
                                                        <div class="small text-truncate">{{ $m->file_name }}</div>
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-truncate" style="max-width:150px;">
                                                    {{ $m->file_name }}
                                                    @if($m->is_featured)
                                                        <span class="badge bg-success ms-1 featured-badge">Featured</span>
                                                    @else
                                                        <span class="badge bg-light text-muted ms-1 featured-badge d-none">Featured</span>
                                                    @endif
                                                </div>

                                                <div class="btn-group">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="setFeaturedMedia('{{ $m->id }}')">
                                                        @if($m->is_featured)
                                                            Featured
                                                        @else
                                                            Make featured
                                                        @endif
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteMedia('{{ $m->id }}')">
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Plain script tag, no x-slot --}}
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const DELETE_BASE_URL = "{{ url('admin/hotels/'.$hotel->id.'/media') }}";

        // Ensure media-grid exists even if initially empty
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.getElementById('media-grid')) {
                const wrapper = document.querySelector('.inv-table-wrapper');
                if (wrapper && !document.getElementById('no-media-message')) {
                    const grid = document.createElement('div');
                    grid.className = 'row g-2';
                    grid.id = 'media-grid';
                    wrapper.appendChild(grid);
                }
            }

            // AJAX upload
            const form = document.getElementById('media-upload-form');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const data = new FormData(form);

                btn.disabled = true;
                btn.innerText = 'Uploading...';

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        body: data
                    });

                    const json = await res.json();
                    btn.disabled = false;
                    btn.innerText = 'Upload';

                    if (!res.ok || !json.success) {
                        alert(json.message || 'Upload failed');
                        return;
                    }

                    // Remove "no media" message if exists
                    const noMsg = document.getElementById('no-media-message');
                    if (noMsg) noMsg.remove();

                    appendMediaCard(json.media, json.url);
                    form.reset();

                } catch (err) {
                    btn.disabled = false;
                    btn.innerText = 'Upload';
                    alert('Upload failed: ' + err.message);
                }
            });
        });

        function appendMediaCard(media, url) {
            const grid = document.getElementById('media-grid');
            if (!grid) return;

            const mime = media.mime_type || '';
            let innerHtml = '';

            if (mime.startsWith('image')) {
                innerHtml = `
                    <a href="${url}" target="_blank">
                        <img src="${url}" class="img-fluid"
                             style="width:100%; height:160px; object-fit:cover;">
                    </a>
                `;
            } else if (mime.startsWith('video')) {
                innerHtml = `
                    <a href="${url}" target="_blank">
                        <video style="width:100%; height:160px; object-fit:cover;" muted>
                            <source src="${url}" type="${mime}">
                        </video>
                    </a>
                `;
            } else {
                innerHtml = `
                    <div
                        style="width:100%; height:160px; display:flex; align-items:center; justify-content:center; background:#f3f4f6;">
                        <a href="${url}" target="_blank"
                           class="text-decoration-none text-center">
                            <div class="text-muted small">Download file</div>
                            <div class="small text-truncate">${media.file_name}</div>
                        </a>
                    </div>
                `;
            }

            const cardHtml = `
                <div class="col-6 col-md-4 col-lg-3 media-card-wrapper" id="media-card-${media.id}">
                    <div class="card" style="border-radius:10px; overflow:hidden;">
                        ${innerHtml}
                        <div class="p-2" style="font-size:12px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-truncate" style="max-width:150px;">
                                    ${media.file_name}
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="deleteMedia('${media.id}')">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            grid.insertAdjacentHTML('afterbegin', cardHtml);
        }

        // Make deleteMedia global for onclick=""
        window.deleteMedia = async function (mediaId) {
            if (!confirm('Delete this media?')) return;

            try {
                const res = await fetch(`${DELETE_BASE_URL}/${mediaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    alert(json.message || 'Delete failed');
                    return;
                }

                const card = document.getElementById('media-card-' + mediaId);
                if (card) card.remove();

                // If no cards left, show "no media" message
                if (!document.querySelector('.media-card-wrapper')) {
                    const wrapper = document.querySelector('.inv-table-wrapper');
                    if (wrapper) {
                        const msg = document.createElement('div');
                        msg.id = 'no-media-message';
                        msg.className = 'p-4 text-center text-muted';
                        msg.innerText = 'No media available for this hotel.';
                        wrapper.appendChild(msg);
                    }
                }
            } catch (err) {
                alert('Delete failed: ' + err.message);
            }
        };

        const FEATURE_BASE_URL = "{{ url('admin/hotels/'.$hotel->id.'/media') }}";

window.setFeaturedMedia = async function (mediaId) {
    try {
        const res = await fetch(`${FEATURE_BASE_URL}/${mediaId}/feature`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        const json = await res.json();

        if (!res.ok || !json.success) {
            alert(json.message || 'Could not set featured image');
            return;
        }

        // Remove featured state from all cards
        document.querySelectorAll('.media-card-wrapper').forEach(function (wrapper) {
            wrapper.querySelector('.card')?.classList.remove('border-success');

            const badge = wrapper.querySelector('.featured-badge');
            if (badge) {
                badge.classList.add('d-none');
            }

            const button = wrapper.querySelector('button[onclick^="setFeaturedMedia"]');
            if (button) {
                button.innerText = 'Make featured';
            }
        });

        // Apply featured state to selected card
        const wrapper = document.getElementById('media-card-' + mediaId);
        if (wrapper) {
            wrapper.querySelector('.card')?.classList.add('border-success');

            const badge = wrapper.querySelector('.featured-badge');
            if (badge) {
                badge.classList.remove('d-none');
            }

            const button = wrapper.querySelector('button[onclick^="setFeaturedMedia"]');
            if (button) {
                button.innerText = 'Featured';
            }
        }

    } catch (err) {
        alert('Error setting featured image: ' + err.message);
    }
};

    </script>
</x-app-layout>
