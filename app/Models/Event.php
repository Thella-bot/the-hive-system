<?php

namespace App\Models;

use App\Enums\PostCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
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
}