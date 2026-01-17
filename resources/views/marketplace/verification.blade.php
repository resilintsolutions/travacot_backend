@extends('layouts.marketplace')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Identity Verification</h1>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        @if ($verification && $verification->status === 'verified')
            <p class="text-sm text-green-700">Your identity is verified.</p>
        @else
            <p class="text-sm text-gray-600">
                Verification is required to resell bookings. The fee is USD 1.50.
            </p>

            <div class="mt-4">
                <label class="block text-sm font-medium">Card Details</label>
                <div id="verify-card" class="mt-2 p-3 border rounded-md bg-white"></div>
                <p id="verify-error" class="text-sm text-red-600 mt-2 hidden"></p>
            </div>

            <button id="verify-btn" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-md">Pay & Start Verification</button>
        @endif
    </div>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#verify-card');

        const verifyBtn = document.getElementById('verify-btn');
        const verifyError = document.getElementById('verify-error');

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

        verifyBtn?.addEventListener('click', async () => {
            verifyError.classList.add('hidden');
            try {
                const fee = await postJson('{{ route('marketplace.verification.fee') }}');
                const result = await stripe.confirmCardPayment(fee.client_secret, {
                    payment_method: { card },
                });

                if (result.error) {
                    verifyError.textContent = result.error.message;
                    verifyError.classList.remove('hidden');
                    return;
                }

                const session = await postJson('{{ route('marketplace.verification.start') }}', {
                    payment_intent_id: fee.payment_intent_id,
                });

                window.location.href = session.url;
            } catch (err) {
                verifyError.textContent = err.message || 'Unable to start verification.';
                verifyError.classList.remove('hidden');
            }
        });
    </script>
@endsection
