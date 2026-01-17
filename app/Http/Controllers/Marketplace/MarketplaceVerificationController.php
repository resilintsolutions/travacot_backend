<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\IdentityVerification;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Stripe\Identity\VerificationSession;
use Stripe\Stripe;

class MarketplaceVerificationController extends Controller
{
    public function show(Request $request)
    {
        $verification = IdentityVerification::where('user_id', $request->user()->id)
            ->latest()
            ->first();

        return view('marketplace.verification', ['verification' => $verification]);
    }

    public function feeIntent(Request $request, StripeService $stripe)
    {
        $intent = $stripe->createPaymentIntent(1.50, 'USD', [
            'purpose' => 'identity_verification_fee',
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
        ]);
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $verification = IdentityVerification::create([
            'user_id' => $request->user()->id,
            'provider' => 'stripe',
            'status' => 'pending',
            'fee_cents' => 150,
            'paid_at' => now(),
            'metadata' => [
                'payment_intent_id' => $data['payment_intent_id'],
            ],
        ]);

        $session = VerificationSession::create([
            'type' => 'document',
            'metadata' => [
                'verification_id' => $verification->id,
                'user_id' => $request->user()->id,
            ],
            'return_url' => route('marketplace.verification.show'),
        ]);

        $verification->update([
            'provider_reference' => $session->id,
            'status' => $session->status ?? 'pending',
        ]);

        return response()->json(['url' => $session->url]);
    }
}
