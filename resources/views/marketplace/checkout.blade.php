@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Checkout</h1>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-lg font-semibold">Guest Details</h2>
            <p class="text-sm text-gray-600 mt-2">Guest name is tied to your account name.</p>
            <div class="mt-4 text-sm text-gray-800">
                {{ $user->name }} · {{ $user->email }}
            </div>

            <h2 class="text-lg font-semibold mt-8">Payment Method</h2>
            <div class="mt-4">
                @if ($paymentMethods->isNotEmpty())
                    <p class="text-sm text-gray-600 mb-2">Saved cards</p>
                    <ul class="text-sm text-gray-700 space-y-1">
                        @foreach ($paymentMethods as $method)
                            <li>**** {{ $method->last4 }} ({{ strtoupper($method->brand ?? 'card') }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-600">No saved cards. Please enter a card below.</p>
                @endif
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium">Card Details</label>
                <div id="card-element" class="mt-2 p-3 border rounded-md bg-white"></div>
                <p id="card-error" class="text-sm text-red-600 mt-2 hidden"></p>
            </div>

            <h2 class="text-lg font-semibold mt-8">Billing Information</h2>
            <div class="grid gap-4 md:grid-cols-2 mt-4">
                <div>
                    <label class="block text-sm font-medium">Country</label>
                    <input class="mt-1 w-full rounded-md border-gray-300" value="{{ $user->country }}" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium">City</label>
                    <input class="mt-1 w-full rounded-md border-gray-300" value="{{ $user->city }}" readonly>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Address</label>
                    <input class="mt-1 w-full rounded-md border-gray-300" value="{{ $user->address }}" readonly>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium">Special Requests</label>
                <textarea class="mt-1 w-full rounded-md border-gray-300" rows="3" id="special-requests"></textarea>
            </div>

            <div class="mt-6 flex items-start gap-2">
                <input type="checkbox" id="agree-cancel" class="mt-1">
                <label for="agree-cancel" class="text-sm text-gray-600">
                    I agree to the cancellation policy.
                </label>
            </div>

            <button id="pay-btn" class="mt-6 bg-indigo-600 text-white px-5 py-2 rounded-md">
                Confirm and Pay
            </button>
        </div>

        <aside class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-lg font-semibold">Your Stay</h2>
            <div class="mt-4 text-sm text-gray-600">
                <div>Check-in: {{ $checkout['check_in'] }}</div>
                <div>Check-out: {{ $checkout['check_out'] }}</div>
                <div>Guests: {{ $checkout['adults'] }} adults, {{ $checkout['children'] }} children</div>
            </div>
            <div class="mt-4 text-sm text-gray-800">
                Rate: {{ number_format($checkout['net'], 2) }} {{ $checkout['currency'] }}
            </div>
        </aside>
    </div>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        const payBtn = document.getElementById('pay-btn');
        const cardError = document.getElementById('card-error');

        const postJson = async (url, payload = {}) => {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json().catch(() => ({}));
            if (!resp.ok) {
                throw data;
            }
            return data;
        };

        payBtn.addEventListener('click', async () => {
            cardError.classList.add('hidden');
            const remark = document.getElementById('special-requests').value;

            if (!document.getElementById('agree-cancel').checked) {
                cardError.textContent = 'You must agree to the cancellation policy.';
                cardError.classList.remove('hidden');
                return;
            }

            try {
                const recheck = await postJson('{{ route('marketplace.checkout.recheck') }}');
                let createResp;

                if (recheck.status === 'changed') {
                    const confirmed = window.confirm('Rate changed to ' + recheck.updated.net + ' ' + recheck.updated.currency + '. Do you accept?');
                    if (!confirmed) {
                        return;
                    }

                    createResp = await postJson('{{ route('marketplace.checkout.accept') }}', {
                        ...recheck.updated,
                        remark
                    });
                } else if (recheck.status === 'ok') {
                    createResp = await postJson('{{ route('marketplace.checkout.create') }}', { remark });
                }

                if (!createResp) {
                    throw new Error('Could not create reservation.');
                }

                const result = await stripe.confirmCardPayment(createResp.client_secret, {
                    payment_method: { card },
                });

                if (result.error) {
                    cardError.textContent = result.error.message;
                    cardError.classList.remove('hidden');
                    return;
                }

                window.location.href = '{{ url('/marketplace/checkout/processing') }}/' + createResp.reservation_id;
            } catch (err) {
                cardError.textContent = err.message || 'Unable to start payment.';
                cardError.classList.remove('hidden');
            }
        });
    </script>
@endsection
