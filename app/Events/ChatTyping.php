<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping
{
    use Dispatchable, SerializesModels;

    public User $user;
    public ChatChannel $channel;
    public bool $isTyping;

    public function __construct(User $user, ChatChannel $channel, bool $isTyping = true)
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->isTyping = $isTyping;
    }
}
