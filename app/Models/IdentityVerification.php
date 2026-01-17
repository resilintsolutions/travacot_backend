<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityVerification extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_reference',
        'status',
        'verified_first_name',
        'verified_last_name',
        'fee_cents',
        'paid_at',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
