<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$user->id}",
            'country' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:50',
            'country_code' => 'sometimes|string|max:10',
        ]);

        if (!empty($data['first_name']) || !empty($data['last_name'])) {
            $first = $data['first_name'] ?? $user->first_name;
            $last = $data['last_name'] ?? $user->last_name;
            $data['name'] = trim($first . ' ' . $last);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password incorrect',
            ], 422);
        }

        $user->password = bcrypt($data['password']);
        $user->save();

        return response()->json(['success' => true]);
    }
}
