<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'              => 'required|email',
            'external_booking_id'=> 'nullable|string|max:100',
        ]);

        $data['user_id'] = optional($request->user())->id;
        $data['status']  = 'new';

        $trip = TripRequest::create($data);

        // optionally dispatch notification/email here

        return response()->json([
            'success' => true,
            'trip'    => $trip,
        ], 201);
    }
}
