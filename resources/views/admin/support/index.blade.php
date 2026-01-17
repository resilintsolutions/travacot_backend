<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl">Customer Support</h2>
                <p class="text-muted small mb-0">Assist customers here</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel Name</th>
                                    <th>Buyer</th>
                                    <th>Seller</th>
                                    <th>Purchase Price</th>
                                    <th>Bookings 24‑Hours</th>
                                    <th>Message</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cases as $case)
                                    @php
                                        $lastMessage = $case->conversation?->messages?->last()?->body ?? 'No messages yet.';
                                        $statusLabel = $case->status === 'solved' ? 'Case Solved' : 'Not solved yet!';
                                        $statusClass = $case->status === 'solved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $case->hotel?->name ?? 'Unknown Hotel' }}</div>
                                            <div class="text-muted small">Country, Phone Number</div>
                                        </td>
                                        <td>{{ $case->buyer?->name ?? '—' }}</td>
                                        <td>{{ $case->seller?->name ?? '—' }}</td>
                                        <td class="text-success fw-semibold">
                                            {{ $case->currency }} {{ number_format($case->purchase_price ?? 0, 2) }}
                                        </td>
                                        <td>{{ $case->bookings_24h }}</td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            {{ \Illuminate\Support\Str::limit($lastMessage, 24) }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.support.show', $case) }}"
                                               class="badge rounded-pill {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No support cases yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $cases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
