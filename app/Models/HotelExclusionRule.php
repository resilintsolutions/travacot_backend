<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelExclusionRule extends Model
{
    protected $fillable = [
        'min_rating',
        'min_reviews',
        'exclude_no_images',
        'exclude_no_description',
        'exclude_inactive',
        'updated_by',
    ];
}
