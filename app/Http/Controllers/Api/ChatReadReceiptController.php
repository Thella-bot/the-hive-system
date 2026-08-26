<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatReadReceiptController extends Controller
{
    /**
     * Mark all messages in a channel as read by the current user.
     */
    public function markAsRead(ChatChannel $channel, Request $request)
    {
        $this->authorize('view', $channel);

        $request->validate([
            'last_read_id' => 'required|integer|exists:messages,id',
        ]);

        $lastReadId = $request->input('last_read_id');

        // Get all messages up to and including last_read_id that the user hasn't read yet
        $messages = $channel->messages()
            ->where('id', '<=', $lastReadId)
            ->where('user_id', '!=', auth()->id())
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('message_reads')
                    ->whereColumn('message_reads.message_id', 'messages.id')
                    ->where('message_reads.user_id', auth()->id());
            })
            ->get();

        // Create read records for each message
        foreach ($messages as $message) {
            $message->readByUsers()->attach(auth()->id(), [
                'read_at' => now(),
            ]);
            $message->increment('read_count');
        }

        return response()->json([
            'marked_as_read' => $messages->count(),
        ]);
    }

    /**
     * Get read receipts for a specific message.
     */
    public function index(ChatChannel $channel, Message $message)
    {
        $this->authorize('view', $channel);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $readByUsers = $message->readByUsers()
            ->orderBy('message_reads.read_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'read_at' => $user->pivot->read_at,
                ];
            });

        return response()->json([
            'message_id' => $message->id,
            'read_count' => $message->read_count,
            'read_by' => $readByUsers,
        ]);
    }

    /**
     * Get unread message count for the current user in a channel.
     */
    public function unreadCount(ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        $totalMessages = $channel->messages()
            ->where('user_id', '!=', auth()->id())
            ->count();

        $readMessages = $channel->messages()
            ->where('user_id', '!=', auth()->id())
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('message_reads')
                    ->whereColumn('message_reads.message_id', 'messages.id')
                    ->where('message_reads.user_id', auth()->id());
            })
            ->count();

        return response()->json([
            'total' => $totalMessages,
            'read' => $readMessages,
            'unread' => $totalMessages - $readMessages,
        ]);
    }
}
