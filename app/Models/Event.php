<?php

namespace App\Models;

use App\Enums\PostCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'start',
        'end',
        'category',
        'target_modules',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'category' => PostCategory::class,
        'target_modules' => 'array',
    ];

    public function targetModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'event_module');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function rsvpFor(User $user): ?EventRsvp
    {
        return $this->rsvps()->where('user_id', $user->id)->first();
    }

    public function toICal(): string
    {
        $start = $this->start->format('Ymd\THis');
        $end = $this->end ? $this->end->format('Ymd\THis') : $this->start->format('Ymd\THis');
        $location = addslashes($this->location ?? '');
        $description = addslashes($this->description ?? '');

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//HBCI//The Hive//EN',
            'BEGIN:VEVENT',
            "UID:event-{$this->id}@hbci.edu",
            "DTSTART:{$start}",
            "DTEND:{$end}",
            "SUMMARY:{$this->title}",
            "DESCRIPTION:{$description}",
            "LOCATION:{$location}",
            'END:VEVENT',
            'END:VCALENDAR',
        ]) . "\r\n";
    }

    // --- Scopes ---

    public function scopeUpcoming($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('start', '>', now());
    }

    public function scopeOngoing($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('start', '<=', now())
            ->where(function ($q) {
                $q->where('end', '>', now())->orWhereNull('end');
            });
    }

    public function scopePast($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('end', '<', now());
    }

    public function scopeForCategory($query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $category);
    }
}
