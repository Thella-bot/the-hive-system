<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementAttachment extends Model
{
    protected $fillable = [
        'announcement_id',
        'name',
        'file_path',
        'size',
        'uploaded_by',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // --- Scopes ---

    public function scopeForAnnouncement($query, int $announcementId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('announcement_id', $announcementId);
    }
}