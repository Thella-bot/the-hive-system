<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth',
        'phone', 'address', 'emergency_contact_name',
        'emergency_contact_phone', 'annual_leave_days', 'leave_balance',
        'profile_picture_path', 'twitter_handle', 'linkedin_profile',
        // Staff fields
        'employee_number', 'department_id', 'designation',
        'specialization', 'bio', 'hire_date',
        // Student fields
        'student_number', 'cohort_id', 'enrollment_date',
        'expected_graduation_date', 'graduation_date', 'status',
        'dietary_restrictions', 'emergency_contact_relationship',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'enrollment_date' => 'date',
        'expected_graduation_date' => 'date',
        'graduation_date' => 'date',
        'dietary_restrictions' => 'array',
    ];

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): MorphTo
    {
        return $this->profileable();
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function cohort(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    // --- Scopes ---

    public function scopeForDepartment($query, int $departmentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeForCohort($query, int $cohortId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('cohort_id', $cohortId);
    }

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeGraduated($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'graduated');
    }

    public function scopeByProfileable($query, int $userId, string $type = User::class): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('profileable_id', $userId)
            ->where('profileable_type', $type);
    }

    // --- Helpers ---

    public function hasSufficientBalanceFor(float $days): bool
    {
        return $this->leave_balance >= $days;
    }
}
