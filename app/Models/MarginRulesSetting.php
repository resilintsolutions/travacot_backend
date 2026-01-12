<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarginRulesSetting extends Model
{
    protected $fillable = [
        'scope',                // global, country, city
        'country',              // ISO
        'city',                 // destination_code
        'default_margin_percent',
        'min_margin_percent',
        'max_margin_percent',
        'is_enabled',
    ];

    public function scopeGlobal($q)
    {
        return $q->where('scope', 'global');
    }

    public function scopeCountry($q, $country)
    {
        return $q->where('scope', 'country')->where('country', $country);
    }

    public function scopeCity($q, $city)
    {
        return $q->where('scope', 'city')->where('city', $city);
    }

    /** PRIORITY: City → Country → Global */
    public static function forLocation($country, $city)
    {
        return self::where(function ($q) use ($country, $city) {

            if ($city) {
                $q->orWhere(fn($x) =>
                    $x->where('scope', 'city')->where('city', $city)
                );
            }

            if ($country) {
                $q->orWhere(fn($x) =>
                    $x->where('scope', 'country')->where('country', $country)
                );
            }

            $q->orWhere('scope', 'global');

        })
        ->orderByRaw("
            CASE
                WHEN scope = 'city' THEN 1
                WHEN scope = 'country' THEN 2
                ELSE 3
            END
        ")
        ->first();
    }
}
