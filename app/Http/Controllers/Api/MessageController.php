<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    public function conversations(Request $request)
    {
        $type = $request->get('type'); // hotels/flights/support

        $q = Conversation::with('messages')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at');

        if ($type) {
            $q->where('type', $type);
        }

        $conversations = $q->get();

        return response()->json(['data' => $conversations]);
    }

    public function show($id, Request $request)
    {
        $conv = Conversation::where('user_id', $request->user()->id)
            ->with('messages.sender')
            ->findOrFail($id);

        return response()->json($conv);
    }

    public function startConversation(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|string',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $user = $request->user();

        $conv = Conversation::create([
            'user_id'         => $user->id,
            'type'            => $data['type'],
            'subject'         => $data['subject'] ?? null,
            'last_message_at' => Carbon::now(),
        ]);

        Message::create([
            'conversation_id' => $conv->id,
            'sender_id'       => $user->id,
            'body'            => $data['message'],
            'is_admin'        => false,
        ]);

        return response()->json([
            'success'         => true,
            'conversation_id' => $conv->id,
        ], 201);
    }

    public function sendMessage($id, Request $request)
    {
        $conv = Conversation::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $msg = Message::create([
            'conversation_id' => $conv->id,
            'sender_id'       => $request->user()->id,
            'body'            => $data['message'],
            'is_admin'        => false,
        ]);

        $conv->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }
}
