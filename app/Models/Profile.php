<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Profile extends Model
{
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function hasSufficientBalanceFor(float $days): bool
    {
        return $this->leave_balance >= $days;
    }
}
