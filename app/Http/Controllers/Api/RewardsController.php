<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function index(Request $request)
    {
        $limit  = (int) $request->get('limit', 10);
        $offset = (int) $request->get('offset', 0);

        // TODO: real rewards query
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
