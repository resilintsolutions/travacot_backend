<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PinnedHotel;

class InventoryPinnedController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $query = PinnedHotel::with(['hotel', 'user'])->orderBy('position');

        if ($search !== '') {
            $query->whereHas('hotel', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        $pinnedHotels = $query->paginate(20)->withQueryString();
        // dd($pinnedHotels);
        return view('admin.inventory.pinned_hotels', compact('pinnedHotels', 'search'));
    }

}
