<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = PaymentMethod::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->get();

        return response()->json(['paymentMethods' => $methods]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'holder_name'  => 'required|string|max:255',
            'card_number'  => 'required|string|min:12|max:19',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year'  => 'required|integer|min:' . date('Y'),
            'cvv'          => 'required|string|min:3|max:4',
            'saveCard'     => 'boolean',
        ]);

        // HERE is where you'd send card to Stripe and get back a token.
        // We'll just fake it and store masked details.
        $last4   = substr(preg_replace('/\D/', '', $data['card_number']), -4);
        $gateway = 'manual'; // or stripe
        $gatewayRef = 'pm_' . uniqid();

        DB::transaction(function () use ($request, $data, $last4, $gateway, $gatewayRef) {
            if ($data['saveCard'] ?? true) {
                // Optionally unset previous default
                PaymentMethod::where('user_id', $request->user()->id)
                    ->update(['is_default' => false]);

                PaymentMethod::create([
                    'user_id'          => $request->user()->id,
                    'brand'            => 'visa',
                    'last4'            => $last4,
                    'expiry_month'     => $data['expiry_month'],
                    'expiry_year'      => $data['expiry_year'],
                    'holder_name'      => $data['holder_name'],
                    'gateway'          => $gateway,
                    'gateway_reference'=> $gatewayRef,
                    'is_default'       => true,
                ]);
            }
        });

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function update($id, Request $request)
    {
        $method = PaymentMethod::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'holder_name' => 'sometimes|string|max:255',
            'is_default'  => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($method, $data, $request) {
            if (array_key_exists('is_default', $data) && $data['is_default']) {
                PaymentMethod::where('user_id', $request->user()->id)
                    ->update(['is_default' => false]);
            }
            $method->update($data);
        });

        return response()->json(['success' => true, 'paymentMethod' => $method]);
    }

    public function destroy($id, Request $request)
    {
        $method = PaymentMethod::where('user_id', $request->user()->id)->findOrFail($id);
        $method->delete();

        return response()->json(['success' => true]);
    }

    public function paymentOptions($propertyId)
    {
        // For now, simple static; you can later check hotel/vendor rules
        return response()->json([
            'cashAvailable' => false,
            'cardAvailable' => true,
        ]);
    }
}
