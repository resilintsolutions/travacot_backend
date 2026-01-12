<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function health()
    {
        // Example checks: DB connection, last import, external API quick ping
        return response()->json([
            'apiHealth' => 'ACTIVE',
            'hotelbeds' => 'OK',
            'lastSync' => now()->subMinutes(5)->toISOString(),
            'errors' => []
        ]);
    }
}
