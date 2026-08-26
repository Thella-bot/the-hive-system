<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatTypingController extends Controller
{
    /**
     * Broadcast typing indicator to channel.
     */
    public function __invoke(ChatChannel $channel, Request $request)
    {
        $this->authorize('view', $channel);

        $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        // Only broadcast if broadcasting is configured and not in testing environment
        if (app()->environment() !== 'testing' && config('broadcasting.default') !== 'null') {
            try {
                broadcast(new \App\Events\ChatTyping(auth()->user(), $channel, $request->input('is_typing')))->toOthers();
            } catch (\Exception $e) {
                Log::debug('Typing indicator broadcast failed: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
