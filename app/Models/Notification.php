<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Represents a system notification.
 *
 * @package App\Models
 */
class Notification extends DatabaseNotification
{
    use HasUuids;

    protected $keyType = 'string';

    public function getIncrementing(): bool
    {
        return false;
    }
}
