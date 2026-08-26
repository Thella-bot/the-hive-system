<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public ChatChannel $channel;
    public bool $isTyping;

    public function __construct(User $user, ChatChannel $channel, bool $isTyping = true)
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn(): array
    {
        $channelType = $this->channel->channel_type;

        $broadcastChannel = match ($channelType) {
            'module' => 'chat.module.' . $this->channel->channel_id,
            'department' => 'chat.department.' . $this->channel->channel_id,
            'general' => 'chat.general',
            'direct' => 'chat.direct.' . $this->channel->id,
            default => 'chat.module.' . $this->channel->channel_id,
        };

        return [new PrivateChannel($broadcastChannel)];
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'is_typing' => $this->isTyping,
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatTyping';
    }
}
