<?php

namespace App\Http\Requests\Hive;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles'    => 'required|array|min:1',
            'roles.*'  => 'exists:roles,name',
            'student_number' => ['nullable', 'string', Rule::unique('profiles', 'student_number')->ignore($user->profile?->id)],
            'programme_id'   => 'nullable|exists:programmes,id',
            'approved_at'    => 'nullable|date',

            'first_name'                 => 'nullable|string|max:255',
            'last_name'                  => 'nullable|string|max:255',
            'date_of_birth'              => 'nullable|date',
            'phone'                      => 'nullable|string|max:20',
            'address'                    => 'nullable|string|max:500',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'annual_leave_days'          => 'nullable|integer',
            'leave_balance'              => 'nullable|integer',
            'employee_number'            => ['nullable', 'string', Rule::unique('profiles', 'employee_number')->ignore($user->profile?->id)],
            'department_id'              => 'nullable|exists:departments,id',
            'designation'                => 'nullable|string|max:255',
            'specialization'             => 'nullable|string|max:255',
            'bio'                        => 'nullable|string',
            'hire_date'                  => 'nullable|date',
            'cohort_id'                  => 'nullable|exists:cohorts,id',
            'enrollment_date'            => 'nullable|date',
            'expected_graduation_date'   => ['nullable', 'date', Rule::when(
                $this->filled('enrollment_date'),
                ['after:enrollment_date']
            )],
            'graduation_date'            => 'nullable|date',
            'status'                     => 'nullable|string',
            'dietary_restrictions'       => 'nullable|array',
        ];
    }
}
