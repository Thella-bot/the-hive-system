<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        $channel = $this->message->chatChannel;

        if (!$channel) {
            // Legacy module-only messages fall back to the old channel
            return [new PrivateChannel('module.' . $this->message->module_id)];
        }

        return match ($channel->channel_type) {
            'module' => [new PrivateChannel('chat.module.' . $channel->channel_id)],
            'department' => [new PrivateChannel('chat.department.' . $channel->channel_id)],
            'general' => [new PrivateChannel('chat.general')],
            'direct' => [new PrivateChannel('chat.direct.' . $channel->id)],
            default => [new PrivateChannel('module.' . $this->message->module_id)],
        };
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message->load('user')];
    }
}
