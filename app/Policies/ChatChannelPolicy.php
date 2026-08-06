<?php

namespace App\Policies;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatChannelPolicy
{
    public function view(User $user, ChatChannel $channel): bool
    {
        return $this->canAccessChannel($user, $channel);
    }

    public function create(User $user, ChatChannel $channel): bool
    {
        return $this->canAccessChannel($user, $channel);
    }

    public function update(User $user, ChatChannel $channel): bool
    {
        return $this->canAccessChannel($user, $channel);
    }

    public function delete(User $user, ChatChannel $channel): bool
    {
        return $this->canAccessChannel($user, $channel);
    }

    protected function canAccessChannel(User $user, ChatChannel $channel): bool
    {
        return match ($channel->channel_type) {
            'general' => $user->isStaff(),
            'department' => $user->profile?->department_id === $channel->channel_id
                || $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']),
            'module' => $channel->module?->students()->where('user_id', $user->id)->exists()
                || $channel->module?->instructors()->where('user_id', $user->id)->exists()
                || $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']),
            'direct' => in_array((string) $user->id, $channel->participants ?? []),
            default => false,
        };
    }
}