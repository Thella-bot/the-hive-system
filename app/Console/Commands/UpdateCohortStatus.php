<?php

namespace App\Console\Commands;

use App\Models\Cohort;
use Illuminate\Console\Command;

class UpdateCohortStatus extends Command
{
    protected $signature = 'cohorts:update-status';

    protected $description = 'Update cohort is_active status based on start and end dates';

    public function handle(): int
    {
        $now = now();

        $deactivated = Cohort::where('is_active', true)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $now)
            ->update(['is_active' => false]);

        $activated = Cohort::where('is_active', false)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->update(['is_active' => true]);

        $this->info("Cohort status updated: {$deactivated} deactivated, {$activated} activated.");

        return self::SUCCESS;
    }
}