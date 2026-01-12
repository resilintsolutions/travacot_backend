<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|string',
            'limit'    => 'nullable|integer',
            'offset'   => 'nullable|integer',
            'location' => 'nullable|string',
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
