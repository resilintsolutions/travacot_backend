<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryHotelsController extends Controller
{
    public function index(Request $request)
    {

        $search   = $request->get('search');
        $supplier = $request->get('supplier'); // e.g. "Hotelbeds"

        $query = Hotel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        if ($supplier) {
            $query->where('vendor', $supplier);
        }

        $query->orderBy('name');

        $hotels = $query->paginate(20)->withQueryString();

        return view('admin.inventory.hotels_list', [
            'hotels'   => $hotels,
            'search'   => $search,
            'supplier' => $supplier,
        ]);
    }

public function uploadMedia(Request $request, Hotel $hotel)
{
    $request->validate([
        'file' => 'required|file|max:5120',
        'collection' => 'nullable|string'
    ]);

    $file = $request->file('file');
    $collection = $request->get('collection', 'images');

    $path = $file->store("hotels/{$hotel->id}/{$collection}", 'public');

    $media = $hotel->media()->create([
        'collection' => $collection,
        'file_name'  => $file->getClientOriginalName(),
        'path'       => $path,
        'mime_type'  => $file->getClientMimeType(),
        'size'       => $file->getSize(),
    ]);

    return response()->json([
        'success' => true,
        'media' => $media,
        'url' => asset('storage/' . $path)
    ]);
}


public function deleteMedia(Hotel $hotel, $mediaId)
{
    $media = $hotel->media()->where('id', $mediaId)->first();

    if (!$media) {
        return response()->json(['success' => false, 'message' => 'Media not found'], 404);
    }

    if ($media->path && Storage::disk('public')->exists($media->path)) {
        Storage::disk('public')->delete($media->path);
    }

    $media->delete();

    return response()->json(['success' => true]);
}

public function setFeaturedMedia(Hotel $hotel, $mediaId)
{
    $media = $hotel->media()->where('id', $mediaId)->first();

    if (! $media) {
        return response()->json(['success' => false, 'message' => 'Media not found'], 404);
    }

    // Unset featured from all media of this hotel
    $hotel->media()->update(['is_featured' => false]);

    // Set this one as featured
    $media->is_featured = true;
    $media->save();

    return response()->json(['success' => true]);
}


}
