<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a PaymentIntent for the given amount & currency.
     *
     * @param  float  $amount   Amount in major units (e.g. 123.45)
     * @param  string $currency ISO currency code (e.g. "EUR", "USD")
     * @param  array  $metadata Extra info to attach on Stripe (reservation_id, hotel_id, etc.)
     * @return \Stripe\PaymentIntent
     */
    public function createPaymentIntent(float $amount, string $currency, array $metadata = []): PaymentIntent
    {
        // Stripe expects the amount in the smallest currency unit (e.g. cents)
        $intAmount = (int) round($amount * 100);

        return PaymentIntent::create([
            'amount'   => $intAmount,
            'currency' => strtolower($currency),

            // This lets Stripe choose best payment methods (cards, etc.)
            'automatic_payment_methods' => [
                'enabled' => true,
            ],

            'metadata' => $metadata,
        ]);
    }
}
