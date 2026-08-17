<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Department;
use App\Services\ReferenceDataService;

class DepartmentObserver
{
    public function __construct(protected ReferenceDataService $referenceData) {}

    public function saved(Department $department): void
    {
        if ($department->isDirty('is_active')) {
            $this->referenceData->flush();
        }
    }

    public function deleted(): void
    {
        $this->referenceData->flush();
    }
}
