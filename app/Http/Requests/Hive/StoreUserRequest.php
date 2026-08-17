<?php
declare(strict_types=1);

namespace App\Http\Requests\Hive;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles'    => 'required|array|min:1',
            'roles.*'  => 'exists:roles,name',

            'first_name'                 => 'nullable|string|max:255',
            'last_name'                  => 'nullable|string|max:255',
            'date_of_birth'              => 'nullable|date',
            'phone'                      => 'nullable|string|max:20',
            'address'                    => 'nullable|string|max:500',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:20',
            'annual_leave_days'          => 'nullable|integer',
            'leave_balance'              => 'nullable|integer',
            'employee_number'            => 'nullable|string|unique:profiles,employee_number',
            'department_id'              => 'nullable|exists:departments,id',
            'designation'                => 'nullable|string|max:255',
            'specialization'             => 'nullable|string|max:255',
            'bio'                        => 'nullable|string',
            'hire_date'                  => 'nullable|date',
            'student_number'             => 'nullable|string|unique:profiles,student_number',
            'cohort_id'                  => 'nullable|exists:cohorts,id',
            'enrollment_date'            => 'nullable|date',
            'expected_graduation_date'   => ['nullable', 'date', 'after:enrollment_date'],
            'graduation_date'            => 'nullable|date',
            'status'                     => 'nullable|string',
            'dietary_restrictions'       => 'nullable|array',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ];
    }
}
