<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\GradableType;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;

trait GradableViewData
{
    /**
     * Get the submission type options for gradable forms.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function getSubmissionTypes(): array
    {
        return [
            [
                'value' => Gradable::SUBMISSION_TYPE_FILE_UPLOAD,
                'label' => 'File Upload',
            ],
            [
                'value' => Gradable::SUBMISSION_TYPE_ONLINE_FILLABLE,
                'label' => 'Online (Fill in the Blank / Short Answer)',
            ],
            [
                'value' => Gradable::SUBMISSION_TYPE_ONLINE_MCQ,
                'label' => 'Online (Multiple Choice)',
            ],
        ];
    }

    /**
     * Get the gradable type options for forms.
     *
     * @return array<int, string>
     */
    protected function getGradableTypeOptions(): array
    {
        return array_map(
            fn(GradableType $type) => $type->value,
            GradableType::cases()
        );
    }

    /**
     * Get modules available for the given user.
     *
     * @param  User  $user
     * @return \Illuminate\Database\Eloquent\Collection<int, Module>
     */
    protected function getModulesForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->hasRole('academic_staff')) {
            return $user->instructedModules()->with('programme')->get();
        }

        if ($user->hasRole('student')) {
            return $user->modules()->with('programme')->get();
        }

        return Module::with('programme')->get();
    }
}
