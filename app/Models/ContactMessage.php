<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'subject', 'message', 'read'];

    protected $casts = [
        'read' => 'boolean',
    ];

    // --- Scopes ---

    public function scopeUnread($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('read', false);
    }

    public function scopeRead($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('read', true);
    }

    public function scopeRecent($query, int $days = 7): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}