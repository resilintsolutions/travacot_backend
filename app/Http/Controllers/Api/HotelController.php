<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\HotelbedsService;


class HotelController extends Controller
{
public function index(Request $req)
{
    $query = Hotel::query();

    if ($req->filled('status')) {
        $query->where('status', $req->status);
    }

    if ($req->filled('country')) {
        $query->where('country', $req->country);
    }

    if ($req->filled('query')) {
        $query->where('name', 'like', '%' . $req->query . '%');
    }

    $limit  = (int) $req->get('limit', 20);
    $offset = (int) $req->get('offset', 0);

    // total BEFORE pagination
    $total = (clone $query)->count();

    // Eager-load MEDIA (collection = images), max 5, featured first
    $hotels = $query
        ->with(['media' => function ($q) {
            $q->where('collection', 'images')
              ->orderByDesc('is_featured')
              ->orderBy('id')
              ->limit(5);
        }])
        ->skip($offset)
        ->take($limit)
        ->get();

    // Turn media relation into plain images[] = [url, url, ...]
    $hotels->transform(function ($hotel) {
        // if you use $m->public_url accessor:
        $hotel->images = $hotel->media
            ->take(5)
            ->map(fn ($m) => $m->public_url) // or $m->url if that's your accessor
            ->values()
            ->all();

        // remove full media objects from response to keep it light
        unset($hotel->media);

        return $hotel;
    });

    return response()->json([
        'meta' => [
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
        ],
        'data' => $hotels,
    ]);
}


    public function store(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string',
            'slug' => 'nullable|string|unique:hotels,slug',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'vendor' => 'nullable|string',
            'vendor_id' => 'nullable|string',
            'status' => 'nullable|in:draft,active,inactive,archived',
        ]);
        $hotel = Hotel::create($data);
        return response()->json(['success' => true, 'data' => $hotel]);
    }

    // public function show(Hotel $hotel)
    // {
    //     $hotel->load('rooms');
    //     return response()->json(['success' => true, 'data' => $hotel]);
    // }

    public function update(Request $req, Hotel $hotel)
    {
        $data = $req->validate([
            'name' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:hotels,slug,' . $hotel->id,
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'status' => 'nullable|in:draft,active,inactive,archived',
        ]);
        $hotel->update($data);
        return response()->json(['success' => true, 'data' => $hotel]);
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return response()->json(['success' => true]);
    }

    
        /**
     * Show hotel detail in admin panel including media gallery.
     */
    public function show(Hotel $hotel, Request $request)
    {
        // eager load media (images collection ordered by position)
        $hotel->load(['media' => function($q) {
            $q->orderBy('collection')->orderBy('position');
        }]);

        // map media with public url (so blade can use ->url)
        $media = $hotel->media->map(function($m) {
            // use Storage::disk('public')->url($m->path)
            $m->public_url = Storage::disk('public')->url($m->path);
            return $m;
        });

        return view('admin.inventory.show', [
            'hotel' => $hotel,
            'media' => $media,
        ]);
    }

    public function deleteMedia(Hotel $hotel, \App\Models\Media $media)
    {
        // ensure media belongs to hotel
        if ($media->mediable_type !== Hotel::class || $media->mediable_id != $hotel->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['success' => true]);
    }

     public static function getContent(int $hotelCode, HotelbedsService $hb)
    {
        $response = $hb->getHotelContent($hotelCode);

        return response()->json($response);
    }

}
