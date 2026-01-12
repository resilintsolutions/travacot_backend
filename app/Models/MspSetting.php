<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MspSetting extends Model
{
    protected $fillable = [
        'scope',
        'country',
        'city',
        'msp_amount',
        'currency'
    ];

    /* GLOBAL MSP */
    public function scopeGlobal($q)
    {
        return $q->where('scope', 'global');
    }

    /* COUNTRY MSP */
    public function scopeCountry($q, ?string $country)
    {
        return $q->where('scope', 'country')
                 ->where('country', $country);
    }

    /* CITY MSP */
    public function scopeCity($q, ?string $city)
    {
        return $q->where('scope', 'city')
                 ->where('city', $city);
    }

    /**
     * Priority resolver (City → Country → Global)
     */
    public static function forLocation(?string $country, ?string $city): ?self
    {
        return self::where(function ($q) use ($country, $city) {

                if ($city) {
                    $q->orWhere(function ($q2) use ($city) {
                        $q2->where('scope', 'city')
                           ->where('city', $city);
                    });
                }

                if ($country) {
                    $q->orWhere(function ($q2) use ($country) {
                        $q2->where('scope', 'country')
                           ->where('country', $country);
                    });
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
