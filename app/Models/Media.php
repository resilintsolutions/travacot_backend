<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'file_name',
        'path',
        'mime_type',
        'size',
        'external_url',
        'meta',
        'collection',
        'position',
        'is_featured'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        // If external URL exists (Hotelbeds), use it
        if (!empty($this->external_url)) {
            return $this->external_url;
        }

        // Otherwise return local storage path
        return $this->path
            ? Storage::disk('public')->url($this->path)
            : null;
    }

}
