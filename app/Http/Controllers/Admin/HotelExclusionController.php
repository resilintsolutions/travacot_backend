<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelExclusion;
use App\Models\HotelExclusionRule;
use App\Services\HotelExclusionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\RecalculateHotelExclusionsJob;

class HotelExclusionController extends Controller
{

    public function index()
    {
        return view('admin.exclusions.index');
    }

    public function rules()
    {
        return HotelExclusionRule::firstOrCreate([]);
    }

    public function updateRules(Request $request)
    {
        $data = $request->validate([
            'min_rating' => 'required|numeric|min:0|max:10',
            'min_reviews' => 'required|integer|min:0',
            'exclude_no_images' => 'boolean',
            'exclude_no_description' => 'boolean',
            'exclude_inactive' => 'boolean',
        ]);

        $rule = HotelExclusionRule::firstOrCreate([]);
        $rule->update($data + ['updated_by' => Auth::id()]);

        return response()->json(['success' => true]);
    }

    public function hotels(Request $request, HotelExclusionService $service)
    {
        $filter = $request->get('filter', 'all');

        $hotels = Hotel::with(['exclusion', 'media'])->paginate(20);

        return $hotels->through(function ($hotel) use ($service, $filter) {
            $eval = $service->evaluateHotelData($hotel);

            if ($filter === 'manual' && $eval['source'] !== 'manual') return null;
            if ($filter === 'automatic' && $eval['source'] !== 'automatic') return null;
            if ($filter === 'inactive' && !in_array('Inactive / Not Bookable', $eval['reasons'])) return null;

            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'location' => "{$hotel->city}, {$hotel->country}",
                'rating' => $hotel->meta['rating'] ?? null,
                'reviews' => $hotel->meta['reviews'] ?? null,
                'status' => $eval,
            ];
        })->filter()->values();
    }

    public function hide(Hotel $hotel)
    {
        HotelExclusion::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'mode' => 'force_hidden',
                'admin_id' => Auth::id(),
                'reason' => 'Manually hidden by admin',
            ]
        );

        return response()->json(['success' => true]);
    }

    public function show(Hotel $hotel)
    {
        HotelExclusion::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'mode' => 'force_visible',
                'admin_id' => Auth::id(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function automatic(Hotel $hotel)
    {
        HotelExclusion::where('hotel_id', $hotel->id)->delete();
        return response()->json(['success' => true]);
    }

    public function bulkHide(Request $request)
    {
        $ids = $request->validate([
            'hotel_ids' => 'required|array|min:1',
            'hotel_ids.*' => 'integer|exists:hotels,id',
        ])['hotel_ids'];

        foreach ($ids as $id) {
            \App\Models\HotelExclusion::updateOrCreate(
                ['hotel_id' => $id],
                [
                    'mode' => 'force_hidden',
                    'admin_id' => Auth::id(),
                    'reason' => 'Bulk hidden by admin',
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function bulkShow(Request $request)
    {
        $ids = $request->validate([
            'hotel_ids' => 'required|array|min:1',
            'hotel_ids.*' => 'integer|exists:hotels,id',
        ])['hotel_ids'];

        foreach ($ids as $id) {
            \App\Models\HotelExclusion::updateOrCreate(
                ['hotel_id' => $id],
                [
                    'mode' => 'force_visible',
                    'admin_id' => Auth::id(),
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function bulkAutomatic(Request $request)
    {
        $ids = $request->validate([
            'hotel_ids' => 'required|array|min:1',
            'hotel_ids.*' => 'integer|exists:hotels,id',
        ])['hotel_ids'];

        \App\Models\HotelExclusion::whereIn('hotel_id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    public function stats(HotelExclusionService $service)
    {
        $total = \App\Models\Hotel::count();
        $autoExcluded = 0;

        \App\Models\Hotel::select('id')->chunk(500, function ($hotels) use (&$autoExcluded, $service) {
            foreach ($hotels as $hotel) {
                $eval = $service->evaluateHotelData($hotel);
                if (!$eval['visible'] && $eval['source'] === 'automatic') {
                    $autoExcluded++;
                }
            }
        });

        return [
            'auto_excluded' => $autoExcluded,
            'remaining' => $total - $autoExcluded,
        ];
    }

    public function recalculate()
    {
        dispatch(new RecalculateHotelExclusionsJob());
        return response()->json(['success' => true]);
    }

}
