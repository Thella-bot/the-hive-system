<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'category', 'target_roles', 'is_pinned', 'expires_at', 'created_by'
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope: only visible to a given user
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $user->roles->pluck('name')->toArray());
        });
    }
}