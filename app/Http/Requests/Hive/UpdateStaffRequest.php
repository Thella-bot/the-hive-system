<?php
declare(strict_types=1);

namespace App\Http\Requests\Hive;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'employee_number' => ['nullable', 'string', Rule::unique('profiles', 'employee_number')->when($staff->profile?->id, fn ($q, $id) => $q->ignore($id))],
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'nullable|date',
        ];
    }
}
