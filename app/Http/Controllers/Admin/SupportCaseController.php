<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportDecisionRequest;
use App\Http\Requests\SupportMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\SupportCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SupportCaseController extends Controller
{
    public function index()
    {
        $cases = SupportCase::with(['hotel', 'buyer', 'seller', 'conversation'])
            ->latest()
            ->paginate(10);

        return view('admin.support.index', [
            'cases' => $cases,
        ]);
    }

    public function show(SupportCase $supportCase)
    {
        $supportCase->load(['hotel', 'buyer', 'seller', 'conversation.messages.sender']);

        if (!$supportCase->conversation) {
            $conversation = Conversation::create([
                'user_id' => $supportCase->buyer_id ?? Auth::id(),
                'type' => 'support',
                'subject' => $supportCase->hotel?->name ?? 'Support Case',
                'last_message_at' => Carbon::now(),
            ]);

            $supportCase->update(['conversation_id' => $conversation->id]);
            $supportCase->load(['conversation.messages.sender']);
        }

        return view('admin.support.show', [
            'case' => $supportCase,
        ]);
    }

    public function sendMessage(SupportCase $supportCase, SupportMessageRequest $request)
    {
        $conversation = $supportCase->conversation;
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id' => $supportCase->buyer_id ?? Auth::id(),
                'type' => 'support',
                'subject' => $supportCase->hotel?->name ?? 'Support Case',
                'last_message_at' => Carbon::now(),
            ]);
            $supportCase->update(['conversation_id' => $conversation->id]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $request->validated('body'),
            'is_admin' => true,
        ]);

        $conversation->update(['last_message_at' => Carbon::now()]);

        return redirect()
            ->route('admin.support.show', $supportCase)
            ->with('success', 'Message sent.');
    }

    public function updateDecision(SupportCase $supportCase, SupportDecisionRequest $request)
    {
        $supportCase->update($request->validated());

        return redirect()
            ->route('admin.support.show', $supportCase)
            ->with('success', 'Decision updated.');
    }
}
