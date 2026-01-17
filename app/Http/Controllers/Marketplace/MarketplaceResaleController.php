<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceResale;
use App\Models\Reservation;
use Illuminate\Http\Request;

class MarketplaceResaleController extends Controller
{
    public function index()
    {
        $resales = MarketplaceResale::with('reservation.hotel')
            ->where('status', 'listed')
            ->orderByDesc('listed_at')
            ->paginate(20);

        return view('marketplace.resales.index', ['resales' => $resales]);
    }

    public function show(MarketplaceResale $resale)
    {
        $resale->load('reservation.hotel');

        return view('marketplace.resales.show', ['resale' => $resale]);
    }

    public function create(Request $request, Reservation $reservation)
    {
        $user = $request->user();

        if ($reservation->user_id !== $user->id) {
            return back()->with('error', 'You can only resell your own reservation.');
        }

        if ($reservation->is_resold || $reservation->resold_to_user_id) {
            return back()->with('error', 'This reservation has already been resold.');
        }

        $verifiedName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $reservationName = $reservation->customer_name
            ?? trim((data_get($reservation->guest_info, 'holder.name') ?? '') . ' ' . (data_get($reservation->guest_info, 'holder.surname') ?? ''));

        if ($verifiedName && $reservationName && strcasecmp($verifiedName, $reservationName) !== 0) {
            return back()->with('error', 'Verified name must match the reservation name.');
        }

        $data = $request->validate([
            'listed_price' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        $resale = MarketplaceResale::create([
            'reservation_id' => $reservation->id,
            'seller_id' => $user->id,
            'listed_price' => $data['listed_price'],
            'currency' => $data['currency'],
            'status' => 'listed',
            'listed_at' => now(),
        ]);

        return redirect()
            ->route('marketplace.resales.show', $resale)
            ->with('success', 'Reservation listed for resale.');
    }

    public function buy(Request $request, MarketplaceResale $resale)
    {
        $user = $request->user();

        if ($resale->status !== 'listed') {
            return back()->with('error', 'This resale is no longer available.');
        }

        if ($resale->seller_id === $user->id) {
            return back()->with('error', 'You cannot buy your own resale.');
        }

        $resale->update([
            'buyer_id' => $user->id,
            'status' => 'sold',
            'sold_at' => now(),
        ]);

        $resale->reservation->update([
            'is_resold' => true,
            'resold_at' => now(),
            'resold_to_user_id' => $user->id,
        ]);

        return redirect()
            ->route('marketplace.resales.show', $resale)
            ->with('success', 'Resale purchased successfully.');
    }
}
