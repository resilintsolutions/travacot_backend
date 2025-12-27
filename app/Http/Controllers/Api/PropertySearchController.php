<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'query'     => 'nullable|string',
            'country'   => 'nullable|string',
            'city'      => 'nullable|string',
            'priceMin'  => 'nullable|numeric',
            'priceMax'  => 'nullable|numeric',
            'ratingMin' => 'nullable|numeric',
            'limit'     => 'nullable|integer',
            'page'      => 'nullable|integer',
        ]);

        $q = Hotel::query()
            ->where('status', 'active')
            ->with('media'); // ✅ eager-load media

        if (!empty($data['query'])) {
            $q->where('name', 'like', '%' . $data['query'] . '%');
        }

        if (!empty($data['country'])) {
            $q->where('country', $data['country']);
        }

        if (!empty($data['city'])) {
            $q->where('city', $data['city']);
        }

        // use isset() so 0 works too
        if (isset($data['priceMin'])) {
            $q->where('lowest_rate', '>=', $data['priceMin']);
        }

        if (isset($data['priceMax'])) {
            $q->where('lowest_rate', '<=', $data['priceMax']);
        }

        if (isset($data['ratingMin'])) {
            $q->where('rating', '>=', $data['ratingMin']);
        }

        $perPage = (int) ($data['limit'] ?? 20);
        $hotels  = $q->paginate($perPage);

        $mapped = $hotels->map(function (Hotel $h) {
            // ✅ Prefer first media image, fallback to meta->images if any
            $firstMedia = $h->media
                ->where('collection', 'images')
                ->sortBy('position')
                ->first();

            $imageUrl = $firstMedia
                ? $firstMedia->url
                : data_get($h->meta, 'images.0.url');

            return [
                'id'           => $h->id,
                'name'         => $h->name,
                'location'     => trim(($h->city ?? '') . ', ' . ($h->country ?? '')),
                'vendor'       => $h->vendor,
                'vendor_id'    => $h->vendor_id,
                'rating'       => $h->rating,
                'lowestRate'   => $h->lowest_rate,
                'currency'     => $h->currency,
                'imageUrl'     => $imageUrl,
                'slug'         => $h->slug,
            ];
        });

        return response()->json([
            'data' => $mapped->values(), // reset keys
            'meta' => [
                'total'       => $hotels->total(),
                'per_page'    => $hotels->perPage(),
                'currentPage' => $hotels->currentPage(),
                'lastPage'    => $hotels->lastPage(),
            ],
        ]);
    }
}
