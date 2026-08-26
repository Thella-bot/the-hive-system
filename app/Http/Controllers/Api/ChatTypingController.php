<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatTyping;
use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ChatTypingController extends Controller
{
    /**
     * Broadcast typing indicator to channel.
     */
    public function store(ChatChannel $channel, Request $request)
    {
        $this->authorize('view', $channel);

        $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        try {
            Event::dispatch(new ChatTyping(auth()->user(), $channel, $request->input('is_typing')));
        } catch (\Exception $e) {
            Log::debug('Typing indicator dispatch failed: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}
