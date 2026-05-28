<?php

namespace App\Models;

use App\Enums\PostCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'category', 'target_roles', 'target_modules', 'is_pinned', 'expires_at', 'created_by'
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_modules' => 'array',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
        'category' => PostCategory::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'announcement_module');
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $user->roles->pluck('name')->toArray());
        });
    }
}