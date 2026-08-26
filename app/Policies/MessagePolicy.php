<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function update(User $user, Message $message): bool
    {
        // Users can edit their own messages
        if ($message->user_id === $user->id) {
            return true;
        }

        // Admins and moderators can edit any message
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }

    public function delete(User $user, Message $message): bool
    {
        // Users can delete their own messages
        if ($message->user_id === $user->id) {
            return true;
        }

        // Admins and moderators can delete any message
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }
}
