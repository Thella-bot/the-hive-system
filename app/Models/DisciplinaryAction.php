<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisciplinaryAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'warning_level',
        'offence',
        'incident_description',
        'hearing_date',
        'effective_date',
        'duration',
        'return_date',
        'campus_access',
        'surrender_date',
        'review_date',
        'grounds',
        'policy_violated',
        'corrective_actions',
        'advisor_name',
        'hr_rep',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'grounds' => 'array',
        'corrective_actions' => 'array',
        'hearing_date' => 'date',
        'effective_date' => 'date',
        'return_date' => 'date',
        'surrender_date' => 'date',
        'review_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
