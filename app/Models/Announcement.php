<?php

namespace App\Models;

use App\Enums\PostCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'body_html', 'category', 'target_roles',
        'target_modules', 'is_pinned', 'priority', 'expires_at', 'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_modules' => 'array',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
        'category' => PostCategory::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'announcement_module');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $user->roles->pluck('name')->toArray());
        });
    }
}
