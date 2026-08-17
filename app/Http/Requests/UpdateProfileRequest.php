<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'               => 'required|string|max:255',
            'last_name'                => 'required|string|max:255',
            'date_of_birth'            => 'nullable|date|before:today',
            'phone'                    => 'nullable|string|max:20',
            'address'                  => 'nullable|string|max:500',
            'emergency_contact_name'   => 'nullable|string|max:255',
            'emergency_contact_phone'  => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'dietary_restrictions'     => 'nullable|string',
            'employee_number'          => 'nullable|string|unique:profiles,employee_number,' . $this->user()->profile?->id,
            'department_id'            => 'nullable|exists:departments,id',
            'designation'              => 'nullable|string|max:255',
            'specialization'           => 'nullable|string|max:255',
            'bio'                      => 'nullable|string',
            'hire_date'                => 'nullable|date',
            'annual_leave_days'        => 'nullable|integer',
            'leave_balance'            => 'nullable|integer',
            'student_number'           => 'nullable|string|unique:profiles,student_number,' . $this->user()->profile?->id,
            'cohort_id'                => 'nullable|exists:cohorts,id',
            'enrollment_date'          => 'nullable|date',
            'expected_graduation_date' => 'nullable|date|after_or_equal:enrollment_date',
            'graduation_date'          => 'nullable|date|after_or_equal:enrollment_date',
            'status'                   => 'nullable|string',
        ];
    }
}