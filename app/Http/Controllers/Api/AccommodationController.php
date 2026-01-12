<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type'   => 'nullable|string',
            'limit'  => 'nullable|integer',
            'offset' => 'nullable|integer',
            'country'=> 'nullable|string',
            'city'   => 'nullable|string',
        ]);

        $limit  = (int) $request->get('limit', 10);
        $offset = (int) $request->get('offset', 0);

        return response()->json([
            'data' => [],
            'meta' => [
                'total'  => 0,
                'limit'  => $limit,
                'offset' => $offset,
            ],
        ]);
    }
}
