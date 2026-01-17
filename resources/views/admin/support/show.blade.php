<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="font-semibold text-xl">Customer Support</h2>
                <p class="text-muted small mb-0">Assist customers here</p>
            </div>
            <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </x-slot>

    <style>
        .chat-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .chat-bubble.buyer { background: #f2f4ff; color: #394b7d; }
        .chat-bubble.seller { background: #1f2143; color: #fff; margin-left: auto; }
        .chat-bubble.admin { background: #e5e7eb; color: #111827; }
        .decision-pill { border-radius: 999px; padding: 6px 12px; font-size: 12px; }
    </style>

    <div class="py-6">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div>
                                    <div class="fw-semibold">{{ $case->hotel?->name ?? 'Unknown Hotel' }}</div>
                                    <div class="text-muted small">Hotel Number</div>
                                </div>
                                <div class="vr"></div>
                                <div class="text-muted small">Buyer Number</div>
                                <div class="vr"></div>
                                <div class="text-muted small">Seller Number</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Chat</label>
                                <div class="d-flex gap-2">
                                    <span class="decision-pill bg-light">Buyer</span>
                                    <span class="decision-pill bg-light">Seller</span>
                                </div>
                            </div>

                            <div class="border rounded p-3" style="height: 420px; overflow-y: auto;">
                                @forelse($case->conversation?->messages ?? [] as $message)
                                    @php
                                        $isBuyer = $case->buyer_id && $message->sender_id === $case->buyer_id;
                                        $isSeller = $case->seller_id && $message->sender_id === $case->seller_id;
                                        $bubbleClass = $message->is_admin ? 'admin' : ($isSeller ? 'seller' : 'buyer');
                                    @endphp
                                    <div class="chat-bubble {{ $bubbleClass }}">
                                        {{ $message->body }}
                                    </div>
                                @empty
                                    <div class="text-muted small">No messages yet.</div>
                                @endforelse
                            </div>

                            <form method="POST" action="{{ route('admin.support.messages.send', $case) }}" class="mt-3 d-flex gap-2">
                                @csrf
                                <input type="text" name="body" class="form-control" placeholder="Write your message here..." required>
                                <button class="btn btn-primary" type="submit">Send</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Decision Console</h6>
                            <div class="text-muted small mb-2">
                                If the seller responds → Proceed with the decision below
                            </div>
                            <div class="text-muted small mb-2">
                                If the seller does not respond → Payout is sent back to buyer
                            </div>
                            <div class="text-muted small mb-2">
                                If the buyer responds → Proceed with the decision below
                            </div>
                            <div class="text-muted small mb-3">
                                If the buyer does not respond → Payout is sent after follow-up to seller
                            </div>

                            <form method="POST" action="{{ route('admin.support.decision', $case) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Did the seller respond?</label>
                                    <div class="d-flex gap-2">
                                        <label class="decision-pill bg-light">
                                            <input type="radio" name="seller_responded" value="1" {{ $case->seller_responded ? 'checked' : '' }}> Yes
                                        </label>
                                        <label class="decision-pill bg-light">
                                            <input type="radio" name="seller_responded" value="0" {{ ! $case->seller_responded ? 'checked' : '' }}> No
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Did the buyer respond?</label>
                                    <div class="d-flex gap-2">
                                        <label class="decision-pill bg-light">
                                            <input type="radio" name="buyer_responded" value="1" {{ $case->buyer_responded ? 'checked' : '' }}> Yes
                                        </label>
                                        <label class="decision-pill bg-light">
                                            <input type="radio" name="buyer_responded" value="0" {{ ! $case->buyer_responded ? 'checked' : '' }}> No
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Decision</label>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="decision-pill bg-success-subtle text-success">
                                            <input type="radio" name="decision" value="payout_continue" {{ $case->decision === 'payout_continue' ? 'checked' : '' }}>
                                            Continue Payout Process Normally
                                        </label>
                                        <label class="decision-pill bg-warning-subtle text-warning">
                                            <input type="radio" name="decision" value="payout_cancel" {{ $case->decision === 'payout_cancel' ? 'checked' : '' }}>
                                            Cancel Payout Process / Send the money back to the buyer
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Case Status</label>
                                    <select class="form-select" name="status">
                                        <option value="open" {{ $case->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="solved" {{ $case->status === 'solved' ? 'selected' : '' }}>Solved</option>
                                    </select>
                                </div>

                                <button class="btn btn-dark" type="submit">Confirm Decision</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
