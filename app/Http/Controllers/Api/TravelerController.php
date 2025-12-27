<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Traveler;
use Illuminate\Http\Request;

class TravelerController extends Controller
{
    public function index(Request $request)
    {
        $travelers = Traveler::where('user_id', $request->user()->id)->get();

        return response()->json(['data' => $travelers]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name'        => 'required|string|max:255',
            'dob'              => 'nullable|date',
            'passport_number'  => 'nullable|string|max:50',
            'nationality'      => 'nullable|string|max:100',
        ]);

        $data['user_id'] = $request->user()->id;

        $traveler = Traveler::create($data);

        return response()->json(['success' => true, 'traveler' => $traveler], 201);
    }

    public function update($id, Request $request)
    {
        $traveler = Traveler::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'full_name'        => 'sometimes|string|max:255',
            'dob'              => 'nullable|date',
            'passport_number'  => 'nullable|string|max:50',
            'nationality'      => 'nullable|string|max:100',
        ]);

        $traveler->update($data);

        return response()->json(['success' => true, 'traveler' => $traveler]);
    }

    public function destroy($id, Request $request)
    {
        $traveler = Traveler::where('user_id', $request->user()->id)->findOrFail($id);
        $traveler->delete();

        return response()->json(['success' => true]);
    }
}
