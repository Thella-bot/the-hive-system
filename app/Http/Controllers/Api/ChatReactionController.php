<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatReactionController extends Controller
{
    /**
     * Get all reactions for a message.
     */
    public function index(ChatChannel $channel, Message $message)
    {
        $this->authorize('view', $channel);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $reactions = $message->reactions()
            ->with('user')
            ->get()
            ->groupBy('emoji')
            ->map(function ($group, $emoji) {
                return [
                    'emoji' => $emoji,
                    'count' => $group->count(),
                    'users' => $group->map(fn ($r) => [
                        'id' => $r->user->id,
                        'name' => $r->user->name,
                    ]),
                    'reacted' => $group->contains('user_id', auth()->id()),
                ];
            })
            ->values();

        return response()->json([
            'reactions' => $reactions,
        ]);
    }

    /**
     * Add a reaction to a message.
     */
    public function store(ChatChannel $channel, Message $message, Request $request)
    {
        $this->authorize('view', $channel);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $reaction = $message->reactions()->firstOrCreate([
            'user_id' => auth()->id(),
            'emoji' => $request->emoji,
        ]);

        return response()->json([
            'id' => $reaction->id,
            'emoji' => $reaction->emoji,
            'created' => $reaction->wasRecentlyCreated,
        ], $reaction->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Remove a reaction from a message.
     */
    public function destroy(ChatChannel $channel, Message $message, Request $request)
    {
        $this->authorize('view', $channel);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $deleted = $message->reactions()
            ->where('user_id', auth()->id())
            ->where('emoji', $request->emoji)
            ->delete();

        return response()->json([
            'deleted' => $deleted > 0,
        ]);
    }
}
