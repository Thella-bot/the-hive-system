<?php

namespace App\Observers;

use App\Services\ReferenceDataService;

class ProgrammeObserver
{
    public function __construct(protected ReferenceDataService $referenceData) {}

    public function saved(): void
    {
        $this->referenceData->flush();
    }

    public function deleted(): void
    {
        $this->referenceData->flush();
    }
}
