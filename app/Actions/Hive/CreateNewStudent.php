<?php

namespace App\Actions\Hive;

use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewStudent
{
    /**
     * Create a new student, assign them a role, and enroll them in modules.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'student_number' => ['nullable', 'string', Rule::unique(Profile::class)->where('profileable_type', User::class)],
            'programme_id' => ['nullable', 'exists:programmes,id'],
        ])->validate();

        $password = $input['password'] ?? 'password';

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($password),
        ]);

        $user->assignRole('student');

        if (!empty($input['programme_id'])) {
            $programme = Programme::with('modules.department')->find($input['programme_id']);
            if ($programme) {
                $moduleIds = $programme->modules->pluck('id');
                $user->modules()->sync($moduleIds);

                $department = $programme->department
                    ?? $programme->modules->first()?->department
                    ?? null;

                $studentNumber = !empty($input['student_number'])
                    ? $input['student_number']
                    : ($department ? IdGenerator::generateStudentId($department->id) : null);

                if ($studentNumber) {
                    $user->profile()->create(['student_number' => $studentNumber]);
                }
            }
        }

        return $user;
    }
}