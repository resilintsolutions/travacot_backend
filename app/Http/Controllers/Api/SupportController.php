<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function contact(Request $request)
    {
        $data = $request->validate([
            'reservationId' => 'nullable|string',
            'message'       => 'required|string',
        ]);

        $user = $request->user();

        // Here you could send an email to support@
        // Mail::to('support@yourdomain.com')->queue(new SupportTicketMail(...));

        // for now just log it
        logger()->info('Support contact', [
            'user_id'        => $user->id,
            'reservation_id' => $data['reservationId'] ?? null,
            'message'        => $data['message'],
        ]);

        return response()->json(['success' => true]);
    }
}
