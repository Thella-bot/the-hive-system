<?php

namespace App\Actions\Hive;

use App\Models\User;
use App\Models\Programme;
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'programme_id' => ['nullable', 'exists:programmes,id'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $user->assignRole('student');

        if (!empty($input['programme_id'])) {
            $programme = Programme::with('modules.department')->find($input['programme_id']);
            if ($programme) {
                // Enroll student in modules
                $moduleIds = $programme->modules->pluck('id');
                $user->modules()->sync($moduleIds);

                // Create profile with a student number
                $department = $programme->modules->first()->department ?? null;
                if ($department) {
                    $studentId = IdGenerator::generate('student', $department->id);
                    $user->profile()->create(['student_number' => $studentId]);
                }
            }
        }

        return $user;
    }
}