<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $guarded = [];
    protected $casts = [
        'payload' => 'array',
    ];
}
