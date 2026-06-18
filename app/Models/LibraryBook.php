<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LibraryBook extends Model
{
    use HasUuids;

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'edition',
        'description',
        'category_id',
        'total_copies',
        'available_copies',
        'location',
        'call_number',
        'cover_image',
        'is_available',
        'is_active',
    ];

    protected $casts = [
        'publish_year' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->whereColumn('available_copies', '>', 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isAvailable(): bool
    {
        return $this->is_available && $this->available_copies > 0;
    }
}