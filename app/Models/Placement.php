<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Placement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'programme_id',
        'organisation_name',
        'organisation_address',
        'supervisor_name',
        'supervisor_contact',
        'start_date',
        'end_date',
        'duration',
        'type',
        'status',
        'learning_objectives',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}
