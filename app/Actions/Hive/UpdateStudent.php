<?php

namespace App\Actions\Hive;

use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateStudent
{
    /**
     * Update an existing student's information.
     *
     * @param  \App\Models\User  $student
     * @param  array  $input
     * @return \App\Models\User
     */
    public function update(User $student, array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->id),
            ],
            'programme_id' => ['nullable', 'exists:programmes,id'],
        ])->validate();

        $student->update([
            'name' => $input['name'],
            'email' => $input['email'],
        ]);

        // Update the student's module enrollments
        if (array_key_exists('programme_id', $input)) {
            if (!empty($input['programme_id'])) {
                $programme = Programme::find($input['programme_id']);
                if ($programme) {
                    $moduleIds = $programme->modules()->pluck('id');
                    $student->modules()->sync($moduleIds);
                }
            } else {
                // If the programme is removed, un-enroll the student from all modules
                $student->modules()->sync([]);
            }
        }

        return $student;
    }
}