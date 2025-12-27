<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Hotel;

class MediaService
{
    /**
     * Download an external URL and store under storage/app/public/hotels/{hotelId}/...
     * Returns Media model on success or null.
     */
    public function importForHotel(int $hotelId, string $externalUrl, string $collection = 'images', array $meta = []): ?Media
    {
        try {
            // simple HEAD to see mime
            $resp = Http::withOptions(['verify' => true])->get($externalUrl);
            if (! $resp->ok()) return null;

            $content = $resp->body();
            $mime = $resp->header('Content-Type') ?: 'application/octet-stream';
            $ext = $this->extensionFromMime($mime) ?: pathinfo(parse_url($externalUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

            $filename = Str::slug(pathinfo(parse_url($externalUrl, PHP_URL_PATH), PATHINFO_FILENAME) ?: 'img') . '-' . Str::random(6) . '.' . $ext;
            $folder = "hotels/{$hotelId}/{$collection}";
            $path = "$folder/$filename";

            Storage::disk('public')->put($path, $content);
            
            $media = Media::create([
                'mediable_type' => Hotel::class,
                'mediable_id'   => $hotelId,
                'collection'    => $collection,
                'file_name'     => $filename,
                'path'          => $path,
                'mime_type'     => $mime,
                'size'          => strlen($content),
                'external_url'  => $externalUrl,
                'meta'          => $meta,
            ]);
            return $media;
        } catch (\Exception $e) {
            logger()->warning('Media import failed: ' . $e->getMessage(), ['url' => $externalUrl]);
            return null;
        }
    }

    protected function extensionFromMime($mime)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'video/mp4'  => 'mp4',
            'application/pdf' => 'pdf',
        ];
        return $map[$mime] ?? null;
    }
}
