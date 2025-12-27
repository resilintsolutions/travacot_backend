<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Hotel extends Model
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'city',
        'vendor',
        'vendor_id',
        'lowest_rate',
        'highest_rate',
        'margin_inc',
        'currency',
        'description',
        'status',
        'meta',
        'country_iso',
        'destination_code',
        'destination_name',
        'address',
        'hotel_email',
        'hotel_phones',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'lowest_rate' => 'float',
        'margin_inc'  => 'float',
        'hotel_phones'=> 'array',
        'meta' => 'array',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'mediable');
    }

    public function images()
    {
        return $this->media()->where('collection', 'images')->orderBy('position');
    }

    public function getFeaturedImageAttribute()
    {
        $media = $this->media
            ->where('collection', 'images')
            ->sortBy('position')
            ->firstWhere('is_featured', true);

        // fallback: first image
        if (!$media) {
            $media = $this->media
                ->where('collection', 'images')
                ->sortBy('position')
                ->first();
        }

        if (!$media) {
            return null;
        }

        // Prefer external URL, else local
        return $media->external_url ?? asset('storage/'.$media->path);
    }

    public function getImagesAttribute()
    {
        return $this->media
            ->where('collection', 'images')
            ->sortBy('position')
            ->map(fn ($m) => $m->url) // <— NOW RETURNS external_url OR storage URL
            ->values()
            ->all();
    }

    // in Hotel model
    public function getDisplayLowestRateAttribute(): ?float
    {
        if (is_null($this->lowest_rate)) {
            return null;
        }

        // assuming margin_inc is percent, e.g. 15 = +15%
        $marginMultiplier = 1 + (($this->margin_inc ?? 0) / 100);

        return round($this->lowest_rate * $marginMultiplier, 2);
    }

    public function exclusion()
    {
        return $this->hasOne(\App\Models\HotelExclusion::class);
    }


}
